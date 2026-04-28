<?php

namespace App\Services;

use App\Models\AdminTask;
use App\Models\Payment;
use App\Models\RateCard;
use App\Models\RateCardApproval;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    public function __construct(
        private readonly ShipmentService $shipmentService,
        private readonly OperationalIssueService $operationalIssueService,
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function requestRateCardApproval(RateCard $rateCard, User $actor, array $requestedAttributes, string $reason): RateCardApproval
    {
        $current = $rateCard->only(array_keys($requestedAttributes));

        $changes = [];

        foreach ($requestedAttributes as $field => $newValue) {
            $oldValue = $current[$field] ?? null;

            if (json_encode($oldValue) !== json_encode($newValue)) {
                $changes[$field] = [
                    'from' => $oldValue,
                    'to' => $newValue,
                ];
            }
        }

        if (empty($changes)) {
            throw ValidationException::withMessages([
                'rate_card' => 'Tidak ada perubahan yang perlu diajukan untuk approval.',
            ]);
        }

        $approval = RateCardApproval::query()
            ->where('rate_card_id', $rateCard->id)
            ->where('status', 'pending')
            ->latest('created_at')
            ->first();

        $payload = [
            'changes' => $changes,
            'reason' => $reason,
            'notes' => 'Menunggu approval perubahan rate card.',
        ];

        if ($approval) {
            $approval->update([
                'requested_by' => $actor->id,
                'changes' => $changes,
                'reason' => $reason,
                'notes' => $payload['notes'],
            ]);

            return $approval->fresh();
        }

        return RateCardApproval::query()->create([
            'rate_card_id' => $rateCard->id,
            'requested_by' => $actor->id,
            'status' => 'pending',
            'changes' => $changes,
            'reason' => $reason,
            'notes' => $payload['notes'],
        ]);
    }

    public function approveRateCardApproval(RateCardApproval $approval, User $approver, ?string $note = null): RateCard
    {
        if ($approver->role !== 'admin') {
            throw ValidationException::withMessages([
                'approver' => 'Hanya admin yang dapat menyetujui perubahan rate card.',
            ]);
        }

        if ($approval->status !== 'pending') {
            throw ValidationException::withMessages([
                'approval' => 'Approval rate card ini sudah diproses.',
            ]);
        }

        $rateCard = $approval->rateCard()->lockForUpdate()->firstOrFail();
        $before = $rateCard->only(array_keys($approval->changes ?? []));

        DB::transaction(function () use ($approval, $approver, $note, $rateCard, $before) {
            $approvedValues = [];

            foreach (($approval->changes ?? []) as $field => $change) {
                $approvedValues[$field] = $change['to'] ?? null;
            }

            $rateCard->forceFill($approvedValues)->save();

            $approval->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'notes' => trim(($approval->notes ? $approval->notes.' ' : '').($note ? 'Approval note: '.$note : '')),
            ]);

            $this->auditLogService->record(
                'rate_card.approved_change',
                $rateCard,
                $approver,
                $before,
                $rateCard->fresh()->only(array_keys($approvedValues)),
                $note ?: 'Perubahan rate card disetujui.',
                [
                    'source' => 'user_action',
                    'is_manual_correction' => false,
                    'correction_reference' => $approval->reason,
                ]
            );
        });

        return $rateCard->fresh(['originZone', 'destinationZone']);
    }

    public function rejectRateCardApproval(RateCardApproval $approval, User $approver, string $reason): RateCardApproval
    {
        if ($approver->role !== 'admin') {
            throw ValidationException::withMessages([
                'approver' => 'Hanya admin yang dapat menolak perubahan rate card.',
            ]);
        }

        if ($approval->status !== 'pending') {
            throw ValidationException::withMessages([
                'approval' => 'Approval rate card ini sudah diproses.',
            ]);
        }

        $approval->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'notes' => trim(($approval->notes ? $approval->notes.' ' : '').'Rejection reason: '.$reason),
        ]);

        $this->auditLogService->record(
            'rate_card.rejected_change',
            $approval,
            $approver,
            [],
            ['status' => 'rejected', 'reason' => $reason],
            $reason,
            [
                'source' => 'user_action',
                'is_manual_correction' => false,
                'correction_reference' => $reason,
            ]
        );

        return $approval->fresh(['rateCard', 'requester', 'approver']);
    }

    public function requestShipmentFinalStatusApproval(Shipment $shipment, User $actor, string $statusCode, array $context = []): AdminTask
    {
        return $this->upsertAdminTask(
            'shipment_final_status_approval',
            'Approval Status Final Shipment '.$shipment->tracking_number,
            $actor,
            'high',
            array_merge([
                'shipment_id' => $shipment->id,
                'status_code' => $statusCode,
                'current_status_id' => $shipment->status_id,
                'location' => $context['location'] ?? null,
                'notes' => $context['notes'] ?? null,
                'reason' => $context['override_reason'] ?? 'Perubahan status final menunggu approval.',
                'override_reason' => $context['override_reason'] ?? 'Perubahan status final menunggu approval.',
            ], $context),
            'Menunggu approval perubahan status final shipment.'
        );
    }

    public function requestShipmentReassignApproval(Shipment $shipment, User $actor, ?int $courierId, ?int $vehicleId, string $reason): AdminTask
    {
        return $this->upsertAdminTask(
            'shipment_reassign_approval',
            'Approval Reassign Shipment '.$shipment->tracking_number,
            $actor,
            'high',
            [
                'shipment_id' => $shipment->id,
                'current_courier_id' => $shipment->courier_id,
                'current_vehicle_id' => $shipment->vehicle_id,
                'courier_id' => $courierId,
                'vehicle_id' => $vehicleId,
                'reason' => $reason,
            ],
            'Menunggu approval reassign shipment yang sudah berjalan.'
        );
    }

    public function requestPaymentManualStatusApproval(Payment $payment, User $actor, string $status, string $reason): AdminTask
    {
        return $this->upsertAdminTask(
            'payment_manual_status_approval',
            'Approval Payment Manual '.$payment->id,
            $actor,
            'high',
            [
                'payment_id' => $payment->id,
                'shipment_id' => $payment->shipment_id,
                'current_status' => $payment->status,
                'requested_status' => $status,
                'reason' => $reason,
            ],
            'Menunggu approval perubahan settlement/refund manual.'
        );
    }

    public function approveAdminTask(AdminTask $task, User $approver, ?string $note = null): Model
    {
        if ($approver->role !== 'admin') {
            throw ValidationException::withMessages([
                'approver' => 'Hanya admin yang dapat menyetujui approval sensitif.',
            ]);
        }

        if (! in_array($task->status, ['pending', 'in_progress'], true)) {
            throw ValidationException::withMessages([
                'task' => 'Task approval ini sudah diproses.',
            ]);
        }

        return DB::transaction(function () use ($task, $approver, $note) {
            $task->update([
                'status' => 'in_progress',
                'started_at' => $task->started_at ?? now(),
            ]);

            $result = match ($task->task_type) {
                'shipment_final_status_approval' => $this->applyShipmentFinalStatusApproval($task, $approver, $note),
                'shipment_reassign_approval' => $this->applyShipmentReassignApproval($task, $approver, $note),
                'payment_manual_status_approval' => $this->applyPaymentManualStatusApproval($task, $approver, $note),
                default => throw ValidationException::withMessages([
                    'task_type' => 'Task approval sensitif tidak dikenali.',
                ]),
            };

            $task->complete([
                'decision' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now()->toIso8601String(),
                'approval_note' => $note,
                'result_model' => class_basename($result),
            ]);

            return $result;
        });
    }

    public function rejectAdminTask(AdminTask $task, User $approver, string $reason): AdminTask
    {
        if ($approver->role !== 'admin') {
            throw ValidationException::withMessages([
                'approver' => 'Hanya admin yang dapat menolak approval sensitif.',
            ]);
        }

        if (! in_array($task->status, ['pending', 'in_progress'], true)) {
            throw ValidationException::withMessages([
                'task' => 'Task approval ini sudah diproses.',
            ]);
        }

        $task->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'result' => [
                'decision' => 'rejected',
                'rejected_by' => $approver->id,
                'rejected_at' => now()->toIso8601String(),
                'reason' => $reason,
            ],
            'notes' => trim(($task->notes ? $task->notes.' ' : '').'Rejection reason: '.$reason),
        ]);

        return $task->fresh();
    }

    private function applyShipmentFinalStatusApproval(AdminTask $task, User $approver, ?string $note): Shipment
    {
        $shipment = Shipment::query()->findOrFail((int) ($task->action_data['shipment_id'] ?? 0));
        $statusCode = (string) ($task->action_data['status_code'] ?? '');

        if ($statusCode === '') {
            throw ValidationException::withMessages([
                'task' => 'Status final yang diminta tidak valid.',
            ]);
        }

        $overrideReason = (string) ($task->action_data['override_reason'] ?? $task->description ?? 'Approval status final');

        return $this->shipmentService->transitionStatus(
            $shipment,
            $statusCode,
            $approver->id,
            $task->action_data['location'] ?? null,
            $task->action_data['notes'] ?? null,
            true,
            trim($overrideReason.($note ? ' '.$note : '')),
        );
    }

    private function applyShipmentReassignApproval(AdminTask $task, User $approver, ?string $note): Shipment
    {
        $shipment = Shipment::query()->findOrFail((int) ($task->action_data['shipment_id'] ?? 0));

        return $this->shipmentService->assignCourier(
            $shipment,
            isset($task->action_data['courier_id']) ? (int) $task->action_data['courier_id'] : null,
            isset($task->action_data['vehicle_id']) ? (int) $task->action_data['vehicle_id'] : null,
            $approver->id
        );
    }

    private function applyPaymentManualStatusApproval(AdminTask $task, User $approver, ?string $note): Payment
    {
        $payment = Payment::query()->with('shipment.customer.user')->findOrFail((int) ($task->action_data['payment_id'] ?? 0));
        $requestedStatus = (string) ($task->action_data['requested_status'] ?? '');

        if ($requestedStatus === '') {
            throw ValidationException::withMessages([
                'task' => 'Status payment yang diminta tidak valid.',
            ]);
        }

        return $this->operationalIssueService->applyPaymentManualOverride(
            $payment,
            $approver,
            ['status' => $requestedStatus],
            trim((string) ($task->action_data['reason'] ?? 'Approval perubahan payment manual').($note ? ' '.$note : ''))
        );
    }

    private function upsertAdminTask(string $taskType, string $title, User $actor, string $priority, array $actionData, string $notes): AdminTask
    {
        $dedupeField = array_key_exists('shipment_id', $actionData)
            ? 'shipment_id'
            : (array_key_exists('payment_id', $actionData) ? 'payment_id' : null);

        $existingTask = AdminTask::query()
            ->where('task_type', $taskType)
            ->whereIn('status', ['pending', 'in_progress'])
            ->when($dedupeField, fn ($query) => $query->where('action_data->'.$dedupeField, $actionData[$dedupeField]))
            ->latest()
            ->first();

        if ($existingTask) {
            $existingTask->update([
                'title' => $title,
                'description' => $actionData['reason'] ?? $existingTask->description,
                'priority' => $priority,
                'action_data' => $actionData,
                'notes' => $notes,
            ]);

            return $existingTask->fresh();
        }

        return AdminTask::query()->create([
            'task_type' => $taskType,
            'title' => $title,
            'description' => $actionData['reason'] ?? $notes,
            'assigned_to' => null,
            'created_by' => $actor->id,
            'status' => 'pending',
            'priority' => $priority,
            'action_data' => $actionData,
            'notes' => $notes,
        ]);
    }
}