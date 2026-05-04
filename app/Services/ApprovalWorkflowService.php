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

    public function requestRateCardCreationApproval(User $actor, array $attributes, string $reason): AdminTask
    {
        return $this->upsertAdminTask(
            'approve_new_rate_card',
            'Approval Rate Card Baru: ' . ($attributes['origin_branch_id'] ?? '?') . ' -> ' . ($attributes['destination_branch_id'] ?? '?'),
            $actor,
            'medium',
            array_merge($attributes, ['reason' => $reason]),
            'Menunggu approval pembuatan rate card baru.'
        );
    }

    public function requestRateCardDeletionApproval(RateCard $rateCard, User $actor, string $reason): AdminTask
    {
        return $this->upsertAdminTask(
            'approve_rate_card_deletion',
            'Approval Hapus Rate Card: ' . ($rateCard->originBranch->name ?? 'N/A') . ' -> ' . ($rateCard->destinationBranch->name ?? 'N/A'),
            $actor,
            'high',
            [
                'rate_card_id' => $rateCard->id,
                'reason' => $reason,
            ],
            'Menunggu approval penghapusan rate card.'
        );
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

        $approval = RateCardApproval::query()->updateOrCreate(
            ['rate_card_id' => $rateCard->id, 'status' => 'pending'],
            [
                'requested_by' => $actor->id,
                'changes' => $changes,
                'reason' => $reason,
                'notes' => $reason, // Use reason as initial notes
            ]
        );

        // Trigger AdminTask for unified queue
        $this->upsertAdminTask(
            'approve_rate_card',
            'Approval Edit Rate Card: ' . ($rateCard->originBranch->name ?? 'N/A') . ' -> ' . ($rateCard->destinationBranch->name ?? 'N/A'),
            $actor,
            'medium',
            [
                'approval_id' => $approval->id,
                'rate_card_id' => $rateCard->id,
                'reason' => $reason,
            ],
            'Menunggu approval perubahan rate card.'
        );

        return $approval;
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

        return $rateCard->fresh(['originBranch', 'destinationBranch']);
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

    public function requestKasirEditApproval(Model $model, User $actor, array $changes, string $reason): AdminTask
    {
        $modelType = class_basename($model);
        $isNew = ! $model->exists && ! ($model->id ?? null);
        $actionName = $isNew ? 'Tambah' : 'Edit';
        
        $identifier = match (true) {
            $model instanceof Shipment => $model->tracking_number ?? 'Baru',
            $model instanceof Payment => (string) ($model->id ?? 'Baru'),
            $model instanceof RateCard => (string) ($model->id ?? 'Baru'),
            default => (string) ($model->id ?? 'Baru'),
        };

        $dedupeField = match (true) {
            $model instanceof Shipment => 'shipment_id',
            $model instanceof Payment => 'payment_id',
            default => null,
        };

        $roleName = ucfirst($actor->role);

        $actionData = [
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'changes' => $changes,
            'reason' => $reason,
            'kasir_id' => $actor->id,
            'kasir_branch_id' => $actor->branch_id,
        ];

        if ($dedupeField && $model->id) {
            $actionData[$dedupeField] = $model->id;
        }

        return $this->upsertAdminTask(
            'kasir_edit_approval',
            sprintf('Approval %s %s %s dari %s', $actionName, $modelType, $identifier, $roleName),
            $actor,
            'medium',
            $actionData,
            sprintf('%s %s mengajukan %s pada %s %s.', $roleName, $actor->name, strtolower($actionName), $modelType, $identifier)
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
        // 1. Prevent Self-Approval
        if ($task->created_by === $approver->id) {
            throw ValidationException::withMessages([
                'approver' => 'Anda tidak dapat menyetujui pengajuan yang Anda buat sendiri.',
            ]);
        }

        // Manager bisa approve kasir_edit_approval dari cabangnya
        // Admin bisa approve semua task
        if ($approver->role === 'manager') {
            $allowedManagerTasks = [
                'kasir_edit_approval',
                'shipment_final_status_approval',
                'shipment_reassign_approval',
                'payment_manual_status_approval',
            ];

            if (! in_array($task->task_type, $allowedManagerTasks, true)) {
                throw ValidationException::withMessages([
                    'approver' => 'Manager hanya dapat menyetujui pengajuan operasional cabang (Rate Card harus via Admin).',
                ]);
            }

            // Check branch scoping
            $taskBranchId = 0;
            if ($task->task_type === 'kasir_edit_approval') {
                $taskBranchId = (int) ($task->action_data['kasir_branch_id'] ?? 0);
            } elseif (in_array($task->task_type, ['shipment_final_status_approval', 'shipment_reassign_approval'], true)) {
                $taskBranchId = (int) Shipment::query()->whereKey($task->action_data['shipment_id'] ?? 0)->value('branch_id');
            } elseif ($task->task_type === 'payment_manual_status_approval') {
                $taskBranchId = (int) Shipment::query()
                    ->whereKey(Payment::query()->whereKey($task->action_data['payment_id'] ?? 0)->value('shipment_id') ?? 0)
                    ->value('branch_id');
            }
            
            if ($taskBranchId !== (int) $approver->branch_id) {
                throw ValidationException::withMessages([
                    'approver' => 'Anda hanya dapat menyetujui pengajuan dari cabang Anda.',
                ]);
            }
        } elseif ($approver->role !== 'admin') {
            throw ValidationException::withMessages([
                'approver' => 'Hanya admin atau manager yang dapat menyetujui approval.',
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
                'shipment_final_status_approval', 'reassign_shipment' => $this->applyShipmentFinalStatusApproval($task, $approver, $note),
                'shipment_reassign_approval' => $this->applyShipmentReassignApproval($task, $approver, $note),
                'payment_manual_status_approval' => $this->applyPaymentManualStatusApproval($task, $approver, $note),
                'approve_new_rate_card' => $this->applyRateCardCreationApproval($task, $approver, $note),
                'approve_rate_card' => $this->applyRateCardTaskApproval($task, $approver, $note),
                'kasir_edit_approval' => $this->applyKasirEditApproval($task, $approver, $note),
                'shipment_pricing_override_approval' => $this->applyShipmentPricingOverrideApproval($task, $approver, $note),
                'approve_rate_card_deletion' => $this->applyRateCardDeletionApproval($task, $approver, $note),
                default => throw ValidationException::withMessages([
                    'task_type' => 'Task approval sensitif tidak dikenali ('.$task->task_type.').',
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
        // 1. Prevent Self-Rejection
        if ($task->created_by === $approver->id) {
            throw ValidationException::withMessages([
                'approver' => 'Anda tidak dapat menolak pengajuan yang Anda buat sendiri.',
            ]);
        }
        // Manager bisa reject kasir_edit_approval dari cabangnya
        if ($approver->role === 'manager') {
            $allowedManagerTasks = [
                'kasir_edit_approval',
                'shipment_final_status_approval',
                'shipment_reassign_approval',
                'payment_manual_status_approval',
            ];

            if (! in_array($task->task_type, $allowedManagerTasks, true)) {
                throw ValidationException::withMessages([
                    'approver' => 'Manager hanya dapat menolak pengajuan operasional cabang.',
                ]);
            }

            // Check branch scoping
            $taskBranchId = 0;
            if ($task->task_type === 'kasir_edit_approval') {
                $taskBranchId = (int) ($task->action_data['kasir_branch_id'] ?? 0);
            } elseif (in_array($task->task_type, ['shipment_final_status_approval', 'shipment_reassign_approval'], true)) {
                $taskBranchId = (int) Shipment::query()->whereKey($task->action_data['shipment_id'] ?? 0)->value('branch_id');
            } elseif ($task->task_type === 'payment_manual_status_approval') {
                $taskBranchId = (int) Shipment::query()
                    ->whereKey(Payment::query()->whereKey($task->action_data['payment_id'] ?? 0)->value('shipment_id') ?? 0)
                    ->value('branch_id');
            }

            if ($taskBranchId !== (int) $approver->branch_id) {
                throw ValidationException::withMessages([
                    'approver' => 'Anda hanya dapat menolak pengajuan dari cabang Anda.',
                ]);
            }
        } elseif ($approver->role !== 'admin') {
            throw ValidationException::withMessages([
                'approver' => 'Hanya admin atau manager yang dapat menolak approval.',
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

        $payment = $this->operationalIssueService->applyPaymentManualOverride(
            $payment,
            $approver,
            ['status' => $requestedStatus],
            trim((string) ($task->action_data['reason'] ?? 'Approval perubahan payment manual').($note ? ' '.$note : ''))
        );

        // Sync payment status to shipment to trigger auto-transition if needed
        $this->shipmentService->syncPaymentStatus($payment->shipment, $payment->status);

        return $payment;
    }

    private function applyRateCardTaskApproval(AdminTask $task, User $approver, ?string $note): RateCard
    {
        $rateCardApprovalId = (int) ($task->action_data['approval_id'] ?? 0);
        
        if ($rateCardApprovalId <= 0) {
            // Fallback for tasks where approval_id might be missing or stored differently
            $rateCardId = (int) ($task->action_data['rate_card_id'] ?? 0);
            $rateCard = RateCard::query()->findOrFail($rateCardId);
            
            // Create a pseudo-approval or just apply if it's a direct task
            $approvedValues = [
                'base_price' => (float) ($task->action_data['new_base_price'] ?? $rateCard->base_price),
                'per_kg_price' => (float) ($task->action_data['new_per_kg_price'] ?? $rateCard->per_kg_price),
            ];
            
            $before = $rateCard->only(array_keys($approvedValues));
            $rateCard->forceFill($approvedValues)->save();
            
            $this->auditLogService->record(
                'rate_card.approved_change_via_task',
                $rateCard,
                $approver,
                $before,
                $rateCard->fresh()->only(array_keys($approvedValues)),
                $note ?: 'Perubahan rate card disetujui melalui task queue.',
                ['source' => 'system_task']
            );
            
            return $rateCard;
        }

        $rateCardApproval = RateCardApproval::query()->findOrFail($rateCardApprovalId);

        return $this->approveRateCardApproval($rateCardApproval, $approver, $note);
    }

    private function applyRateCardCreationApproval(AdminTask $task, User $approver, ?string $note): RateCard
    {
        // Only admin can approve this
        if ($approver->role !== 'admin') {
            throw ValidationException::withMessages(['approver' => 'Hanya admin yang dapat menyetujui rate card baru.']);
        }

        $data = $task->action_data;
        $attributes = collect($data)->except(['reason', 'kasir_id', 'kasir_branch_id'])->toArray();

        $rateCard = RateCard::query()->create($attributes);

        $this->auditLogService->record(
            'rate_card.create_approved',
            $rateCard,
            $approver,
            [],
            $rateCard->fresh()->toArray(),
            $note ?: 'Pembuatan rate card baru disetujui oleh admin.',
            ['source' => 'approval_workflow', 'requester_id' => $task->created_by]
        );

        return $rateCard;
    }

    private function applyKasirEditApproval(AdminTask $task, User $approver, ?string $note): Model
    {
        $modelClass = (string) ($task->action_data['model_type'] ?? '');
        $modelId = (int) ($task->action_data['model_id'] ?? 0);
        $changes = (array) ($task->action_data['changes'] ?? []);

        if ($modelClass === '' || empty($changes)) {
            throw ValidationException::withMessages([
                'task' => 'Data perubahan tidak valid.',
            ]);
        }

        // Filter out fields that are not columns (like 'reason' from the request)
        $changes = collect($changes)->except(['reason', '_token', '_method'])->toArray();

        // For new model creation (model_id is null/0)
        if ($modelId <= 0) {
            $model = new $modelClass;
            $model->forceFill($changes)->save();

            $this->auditLogService->record(
                'kasir_edit.approved_create',
                $model,
                $approver,
                [],
                $model->fresh()->toArray(),
                $note ?: 'Pembuatan baru dari kasir disetujui oleh manager.',
                ['source' => 'kasir_approval', 'kasir_id' => $task->action_data['kasir_id'] ?? null]
            );

            return $model;
        }

        $model = $modelClass::query()->findOrFail($modelId);
        if ($model instanceof Payment) {
            $model->load('shipment');
        }
        $before = $model->only(array_keys($changes));

        $model->forceFill($changes)->save();

        if ($model instanceof Payment && $model->shipment) {
            $this->shipmentService->syncPaymentStatus($model->shipment, $model->status);
        }

        $this->auditLogService->record(
            'kasir_edit.approved',
            $model,
            $approver,
            $before,
            $model->fresh()->only(array_keys($changes)),
            $note ?: 'Perubahan dari kasir disetujui oleh manager.',
            ['source' => 'kasir_approval', 'kasir_id' => $task->action_data['kasir_id'] ?? null]
        );

        return $model->fresh();
    }

    private function applyShipmentPricingOverrideApproval(AdminTask $task, User $approver, ?string $note): Shipment
    {
        $shipment = Shipment::query()->findOrFail((int) ($task->action_data['shipment_id'] ?? 0));

        return $this->shipmentService->approvePricingOverrideRequest(
            $shipment,
            $approver,
            $note
        );
    }

    private function applyRateCardDeletionApproval(AdminTask $task, User $approver, ?string $note): RateCard
    {
        if ($approver->role !== 'admin') {
            throw ValidationException::withMessages(['approver' => 'Hanya admin yang dapat menyetujui penghapusan rate card.']);
        }

        $rateCardId = (int) ($task->action_data['rate_card_id'] ?? 0);
        $rateCard = RateCard::query()->findOrFail($rateCardId);

        $before = $rateCard->toArray();

        $rateCard->delete();

        $this->auditLogService->record(
            'rate_card.delete_approved',
            $rateCard,
            $approver,
            $before,
            [],
            $note ?: 'Penghapusan rate card disetujui oleh admin.',
            ['source' => 'approval_workflow', 'requester_id' => $task->created_by]
        );

        return $rateCard;
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