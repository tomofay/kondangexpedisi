<?php

namespace App\Services;

use App\Models\AdminTask;
use App\Models\Branch;
use App\Services\AuditLogService;
use App\Models\Payment;
use App\Models\ShipmentItem;
use App\Models\RateCard;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTracking;
use App\Models\Zone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    private ?bool $hasDestinationBranchColumn = null;

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService
    )
    {
    }

    public function generateTrackingNumber(int $branchId): string
    {
        $branchCode = Branch::query()->whereKey($branchId)->value('code') ?: 'GEN';
        $prefix = config('expedition.tracking_number.prefix', 'SXP');
        $dateFormat = config('expedition.tracking_number.date_format', 'Ymd');
        $random = Str::upper(Str::random(5));

        return sprintf('%s-%s-%s-%s', $prefix, $branchCode, now()->format($dateFormat), $random);
    }

    public function allowedShipmentStatusCodes(): array
    {
        return array_values(config('expedition.shipment_statuses', []));
    }

    public function finalShipmentStatusCodes(): array
    {
        return array_values(config('expedition.shipment_status_flow.final_statuses', ['delivered', 'cancelled', 'returned']));
    }

    public function allowedNextShipmentStatusCodes(string $currentStatusCode): array
    {
        $transitions = config('expedition.shipment_status_flow.transitions', []);

        return array_values($transitions[$currentStatusCode] ?? []);
    }

    public function isFinalShipmentStatus(string $statusCode): bool
    {
        return in_array($statusCode, $this->finalShipmentStatusCodes(), true);
    }

    public function canForceShipmentStatusOverride(?User $actor): bool
    {
        return $actor !== null && in_array($actor->role, config('expedition.shipment_status_flow.override_roles', ['admin']), true);
    }

    public function validateShipmentStatusTransition(Shipment $shipment, string $statusCode, ?User $actor = null, bool $forceTransition = false): void
    {
        $allowedStatusCodes = $this->allowedShipmentStatusCodes();

        if (! in_array($statusCode, $allowedStatusCodes, true)) {
            throw ValidationException::withMessages([
                'status_code' => 'Status shipment tidak valid.',
            ]);
        }

        $shipment->loadMissing('status');
        $currentStatusCode = $shipment->status?->code;

        if ($currentStatusCode === $statusCode) {
            throw ValidationException::withMessages([
                'status_code' => 'Shipment sudah berada pada status tersebut.',
            ]);
        }

        if ($shipment->status?->is_final) {
            if (! $forceTransition) {
                throw ValidationException::withMessages([
                    'status_code' => 'Shipment yang sudah final tidak bisa dipindahkan ke status lain.',
                ]);
            }

            if (! $this->canForceShipmentStatusOverride($actor)) {
                throw ValidationException::withMessages([
                    'status_code' => 'Hanya admin yang dapat melakukan override status final.',
                ]);
            }

            return;
        }

        $allowedNextStatuses = $this->allowedNextShipmentStatusCodes($currentStatusCode ?? '');

        if (! in_array($statusCode, $allowedNextStatuses, true)) {
            throw ValidationException::withMessages([
                'status_code' => 'Transisi status tidak valid. Ikuti urutan bisnis yang sudah ditentukan.',
            ]);
        }
    }

    public function calculateTotalAmount(array $data): array
    {
        $destinationZone = Zone::query()->find($data['zone_id'] ?? null);
        $originZoneId = $data['origin_zone_id'] ?? null;

        if (! $originZoneId && ! empty($data['branch_id'])) {
            $originZoneId = Branch::query()->whereKey($data['branch_id'])->value('zone_id');
        }

        $originZone = $originZoneId ? Zone::query()->find($originZoneId) : null;
        $weight = (float) ($data['total_weight_kg'] ?? 0);
        $serviceType = $data['service_type'] ?? 'regular';
        $insuranceAmount = (float) ($data['insurance_amount'] ?? 0);
        $adminFee = (float) ($data['admin_fee'] ?? 2500);

        if (! $destinationZone) {
            throw ValidationException::withMessages([
                'zone_id' => 'Zona tujuan wajib dipilih agar biaya kirim bisa dihitung.',
            ]);
        }

        if (! $originZone) {
            throw ValidationException::withMessages([
                'branch_id' => 'Cabang asal harus memiliki zona aktif agar biaya kirim bisa dihitung.',
            ]);
        }

        $rateCardQuery = RateCard::query()
            ->where('origin_zone_id', $originZone->id)
            ->where('destination_zone_id', $destinationZone->id)
            ->where('service_type', $serviceType)
            ->where('is_active', true)
            ->where('min_weight_kg', '<=', $weight)
            ->where(function ($query) use ($weight) {
                $query->whereNull('max_weight_kg')->orWhere('max_weight_kg', '>=', $weight);
            })
            ->orderByDesc('min_weight_kg');

        $rateCard = $rateCardQuery->first();

        if (! $rateCard) {
            $rateCard = RateCard::query()
                ->where('origin_zone_id', $originZone->id)
                ->where('destination_zone_id', $destinationZone->id)
                ->where('service_type', $serviceType)
                ->where('is_active', true)
                ->orderBy('min_weight_kg')
                ->first();
        }

        if (! $rateCard) {
            $rateCard = RateCard::query()
                ->where('origin_zone_id', $originZone->id)
                ->where('destination_zone_id', $destinationZone->id)
                ->where('is_active', true)
                ->orderBy('service_type')
                ->orderBy('min_weight_kg')
                ->first();
        }

        if (! $rateCard) {
            $fallbackEnabled = (bool) config('expedition.pricing.fallback.enabled', true);

            if (! $fallbackEnabled) {
                throw ValidationException::withMessages([
                    'zone_id' => 'Rate card untuk rute zona asal/tujuan dan service ini belum tersedia.',
                ]);
            }

            $fallbackBasePrice = (float) config('expedition.pricing.fallback.base_price', 15000);
            $fallbackPerKgPrice = (float) config('expedition.pricing.fallback.per_kg_price', 7000);
            $applyDestinationMultiplier = (bool) config('expedition.pricing.fallback.apply_destination_multiplier', true);

            $zoneMultiplier = $applyDestinationMultiplier ? (float) $destinationZone->multiplier : 1;
            $baseAmount = ($fallbackBasePrice * $zoneMultiplier) + ($fallbackPerKgPrice * max($weight, 1));
            $subtotalAmount = (int) round($baseAmount);
            $totalAmount = $subtotalAmount + (int) round($insuranceAmount) + (int) round($adminFee);

            return [
                'subtotal_amount' => $subtotalAmount,
                'insurance_amount' => (int) round($insuranceAmount),
                'admin_fee' => (int) round($adminFee),
                'total_amount' => $totalAmount,
                'rate_card_id' => null,
                'calculation_mode' => 'fallback_estimate',
                'requires_manual_approval' => true,
                'calculation_note' => 'Rate card tidak ditemukan. Ongkir dihitung memakai fallback estimate dan menunggu review manual.',
            ];
        }

        $zoneMultiplier = (float) $destinationZone->multiplier;
        $baseAmount = ((float) $rateCard->base_price * $zoneMultiplier) + ((float) $rateCard->per_kg_price * max($weight, 1));
        $subtotalAmount = (int) round($baseAmount);
        $totalAmount = $subtotalAmount + (int) round($insuranceAmount) + (int) round($adminFee);

        return [
            'subtotal_amount' => $subtotalAmount,
            'insurance_amount' => (int) round($insuranceAmount),
            'admin_fee' => (int) round($adminFee),
            'total_amount' => $totalAmount,
            'rate_card_id' => $rateCard->id,
            'calculation_mode' => 'rate_card',
            'requires_manual_approval' => false,
            'calculation_note' => null,
        ];
    }

    public function assignCourier(Shipment $shipment, ?int $courierId, ?int $vehicleId, ?int $actorId = null): Shipment
    {
        $before = $shipment->only(['courier_id', 'vehicle_id']);
        $shipment->loadMissing('branch');

        DB::transaction(function () use ($shipment, $courierId, $vehicleId, $actorId, $before) {
            $shipment->update([
                'courier_id' => $courierId,
                'vehicle_id' => $vehicleId,
            ]);

            if ($courierId) {
                $assignedStatusId = ShipmentStatus::query()->where('code', 'in_transit')->value('id');
                $shipment->trackings()->create([
                    'status_id' => $assignedStatusId,
                    'created_by' => $actorId,
                    'location' => $shipment->branch?->city,
                    'notes' => 'Kurir ditugaskan ke shipment ini.',
                    'event_at' => now(),
                ]);
            }

            $this->auditLogService->record(
                'shipment.assign_courier',
                $shipment,
                User::query()->find($actorId),
                $before,
                $shipment->fresh()->only(['courier_id', 'vehicle_id']),
                'Kurir ditetapkan ke shipment.'
            );
        });

        return $shipment->fresh(['branch', 'courier', 'vehicle', 'status']);
    }

    public function transitionStatus(
        Shipment $shipment,
        string $statusCode,
        ?int $actorId = null,
        ?string $location = null,
        ?string $notes = null,
        bool $forceTransition = false,
        ?string $overrideReason = null,
        ?float $gpsLat = null,
        ?float $gpsLng = null,
        ?float $gpsAccuracyM = null
    ): Shipment
    {
        $actor = User::query()->find($actorId);
        $this->validateShipmentStatusTransition($shipment, $statusCode, $actor, $forceTransition);

        $status = ShipmentStatus::query()->where('code', $statusCode)->first();

        if (! $status) {
            throw ValidationException::withMessages([
                'status_code' => 'Status shipment tidak valid.',
            ]);
        }

        $before = $shipment->only(['status_id', 'current_status_at', 'delivered_at']);

        $trackingNotes = $notes;

        if ($forceTransition) {
            $trackingNotes = trim(sprintf(
                'Manual override: %s%s',
                $overrideReason ? $overrideReason.'. ' : '',
                $notes ?: 'Perubahan status final disetujui admin.'
            ));
        }

        $shipment->update([
            'status_id' => $status->id,
            'current_status_at' => now(),
            'delivered_at' => $statusCode === 'delivered' ? now() : $shipment->delivered_at,
        ]);

        $shipment->trackings()->create([
            'status_id' => $status->id,
            'created_by' => $actorId,
            'location' => $location,
            'gps_lat' => $gpsLat,
            'gps_lng' => $gpsLng,
            'gps_accuracy_m' => $gpsAccuracyM,
            'notes' => $trackingNotes ?: 'Status shipment diperbarui menjadi '.$status->name,
            'event_at' => now(),
        ]);

        $this->auditLogService->record(
            'shipment.transition_status',
            $shipment,
            $actor,
            $before,
            $shipment->fresh()->only(['status_id', 'current_status_at', 'delivered_at']),
            $trackingNotes
        );

        if ($actor?->role === 'courier') {
            $this->notificationService->notifyShipmentCustomer(
                $shipment->fresh(['customer.user']),
                'shipment_status_updated',
                'Status Pengiriman Diperbarui',
                sprintf('Shipment %s sekarang berstatus %s.', $shipment->tracking_number, $status->name),
                [
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'status_code' => $status->code,
                    'status_name' => $status->name,
                    'location' => $location,
                ],
                'medium'
            );
        }

        return $shipment->fresh(['status', 'trackings.status']);
    }

    public function createShipment(array $data, ?User $actor = null): Shipment
    {
        $manualOverride = (bool) ($data['manual_override'] ?? false);
        $amount = $manualOverride
            ? [
                'subtotal_amount' => (int) round((float) ($data['subtotal_amount'] ?? 0)),
                'insurance_amount' => (int) round((float) ($data['insurance_amount'] ?? 0)),
                'admin_fee' => (int) round((float) ($data['admin_fee'] ?? 0)),
                'total_amount' => (int) round((float) ($data['total_amount'] ?? 0)),
                'calculation_mode' => 'manual_input',
                'requires_manual_approval' => false,
                'calculation_note' => null,
            ]
            : $this->calculateTotalAmount($data);
        $pendingStatusId = ShipmentStatus::query()->where('code', 'pending')->value('id');

        $requiresPricingApproval = ! $manualOverride && (bool) ($amount['requires_manual_approval'] ?? false);
        $calculationNote = $amount['calculation_note'] ?? null;

        $payload = [
            'tracking_number' => $this->generateTrackingNumber((int) $data['branch_id']),
            'customer_id' => $data['customer_id'] ?? null,
            'branch_id' => $data['branch_id'],
            'courier_id' => $data['courier_id'] ?? null,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'zone_id' => $data['zone_id'] ?? null,
            'status_id' => $pendingStatusId,
            'sender_name' => $data['sender_name'],
            'sender_phone' => $data['sender_phone'],
            'sender_address' => $data['sender_address'],
            'recipient_name' => $data['recipient_name'],
            'recipient_phone' => $data['recipient_phone'],
            'recipient_address' => $data['recipient_address'],
            'service_type' => $data['service_type'],
            'total_weight_kg' => $data['total_weight_kg'],
            'total_volume' => $data['total_volume'] ?? 0,
            'subtotal_amount' => $amount['subtotal_amount'],
            'insurance_amount' => $amount['insurance_amount'],
            'admin_fee' => $amount['admin_fee'],
            'total_amount' => $amount['total_amount'],
            'auto_subtotal_amount' => $manualOverride ? null : $amount['subtotal_amount'],
            'auto_insurance_amount' => $manualOverride ? null : $amount['insurance_amount'],
            'auto_admin_fee' => $manualOverride ? null : $amount['admin_fee'],
            'auto_total_amount' => $manualOverride ? null : $amount['total_amount'],
            'corrected_total_amount' => $manualOverride ? $amount['total_amount'] : null,
            'is_cod' => (bool) ($data['is_cod'] ?? false),
            'cod_amount' => $data['cod_amount'] ?? 0,
            'payment_status' => 'pending',
            'processing_status' => $requiresPricingApproval
                ? 'needs_manual_review'
                : ($manualOverride ? 'needs_manual_review' : 'ok'),
            'processing_error' => $requiresPricingApproval
                ? $calculationNote
                : ($manualOverride ? 'Shipment dibuat melalui koreksi manual.' : null),
            'pricing_mode' => $manualOverride ? 'manual' : 'auto',
            'pricing_approval_status' => $requiresPricingApproval ? 'pending' : ($manualOverride ? 'approved' : 'not_required'),
            'pricing_approved_by' => $manualOverride ? $actor?->id : null,
            'pricing_approved_at' => $manualOverride ? now() : null,
            'manual_override_by' => $manualOverride ? $actor?->id : null,
            'manual_override_reason' => $manualOverride ? ($data['manual_override_reason'] ?? null) : null,
            'manual_override_at' => $manualOverride ? now() : null,
            'current_status_at' => now(),
            'estimated_delivery_at' => $data['estimated_delivery_at'] ?? now()->addDays(2),
            'delivered_at' => null,
            'notes' => $data['notes'] ?? null,
        ];

        if ($this->supportsDestinationBranchColumn()) {
            $payload['destination_branch_id'] = $data['destination_branch_id'] ?? null;
        }

        $shipment = DB::transaction(function () use ($payload, $pendingStatusId, $actor, $data) {
            $shipment = Shipment::query()->create($payload);

            $shipment->loadMissing('branch');

            $shipment->trackings()->create([
                'status_id' => $pendingStatusId,
                'created_by' => $actor?->id,
                'location' => $shipment->branch?->city,
                'notes' => 'Shipment dibuat dan menunggu proses pickup.',
                'event_at' => now(),
            ]);

            $autoPayment = Payment::query()->create([
                'shipment_id' => $shipment->id,
                'customer_id' => $shipment->customer_id,
                'processed_by' => $actor?->id,
                'method' => (bool) ($data['is_cod'] ?? false) ? 'cod' : 'midtrans',
                'status' => 'pending',
                'amount' => $shipment->total_amount,
                'notes' => 'Payment otomatis dibuat saat shipment dibuat berdasarkan perhitungan rate card.',
            ]);

            $this->auditLogService->record(
                'payment.auto_create',
                $autoPayment,
                $actor,
                [],
                $autoPayment->only(['shipment_id', 'customer_id', 'method', 'status', 'amount']),
                'Payment pending otomatis dibuat dari shipment baru.'
            );

            $this->auditLogService->record(
                'shipment.create',
                $shipment,
                $actor,
                [],
                $shipment->fresh()->only(['tracking_number', 'branch_id', 'status_id', 'payment_status', 'total_amount']),
                'Shipment dibuat.'
            );

            return $shipment;
        });

        if ($requiresPricingApproval) {
            $this->createFallbackPricingReviewTask($shipment, $actor, (string) $calculationNote);
        }

        return $shipment->fresh(['branch', 'status', 'trackings', 'payments']);
    }

    public function requestPricingOverrideApproval(Shipment $shipment, User $actor, array $amounts, string $reason): AdminTask
    {
        $before = $shipment->only([
            'processing_status',
            'processing_error',
            'pricing_approval_status',
        ]);

        $activeTask = AdminTask::query()
            ->where('task_type', 'shipment_pricing_override_approval')
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('action_data->shipment_id', $shipment->id)
            ->latest()
            ->first();

        $actionData = [
            'shipment_id' => $shipment->id,
            'current_amounts' => [
                'subtotal_amount' => (float) $shipment->subtotal_amount,
                'insurance_amount' => (float) $shipment->insurance_amount,
                'admin_fee' => (float) $shipment->admin_fee,
                'total_amount' => (float) $shipment->total_amount,
            ],
            'proposed_amounts' => [
                'subtotal_amount' => (float) $amounts['subtotal_amount'],
                'insurance_amount' => (float) $amounts['insurance_amount'],
                'admin_fee' => (float) $amounts['admin_fee'],
                'total_amount' => (float) $amounts['total_amount'],
            ],
            'reason' => $reason,
            'requested_by' => $actor->id,
            'requested_at' => now()->toIso8601String(),
        ];

        if ($activeTask) {
            $activeTask->update([
                'description' => $reason,
                'priority' => 'high',
                'action_data' => $actionData,
            ]);

            $task = $activeTask;
        } else {
            $task = AdminTask::query()->create([
                'task_type' => 'shipment_pricing_override_approval',
                'title' => 'Approval Override Tarif Shipment '.$shipment->tracking_number,
                'description' => $reason,
                'assigned_to' => null,
                'created_by' => $actor->id,
                'status' => 'pending',
                'priority' => 'high',
                'action_data' => $actionData,
                'notes' => 'Menunggu approval admin untuk koreksi tarif manual.',
            ]);
        }

        $shipment->forceFill([
            'processing_status' => 'needs_manual_review',
            'processing_error' => 'Menunggu approval override tarif manual.',
            'pricing_approval_status' => 'pending',
            'pricing_approved_by' => null,
            'pricing_approved_at' => null,
        ])->save();

        $this->auditLogService->record(
            'shipment.pricing_override_requested',
            $shipment,
            $actor,
            $before,
            $shipment->fresh()->only([
                'processing_status',
                'processing_error',
                'pricing_approval_status',
                'pricing_approved_by',
                'pricing_approved_at',
            ]),
            $reason,
            [
                'source' => 'user_action',
                'is_manual_correction' => true,
                'correction_reference' => $reason,
            ]
        );

        return $task;
    }

    public function approvePricingOverrideRequest(Shipment $shipment, User $approver, ?string $approvalNote = null): Shipment
    {
        if ($approver->role !== 'admin') {
            throw ValidationException::withMessages([
                'approver' => 'Hanya admin yang dapat menyetujui override tarif.',
            ]);
        }

        $task = AdminTask::query()
            ->where('task_type', 'shipment_pricing_override_approval')
            ->where('status', 'pending')
            ->where('action_data->shipment_id', $shipment->id)
            ->latest()
            ->first();

        if (! $task) {
            throw ValidationException::withMessages([
                'shipment' => 'Tidak ada permintaan override tarif yang menunggu approval.',
            ]);
        }

        $proposed = $task->action_data['proposed_amounts'] ?? null;

        if (! is_array($proposed)) {
            throw ValidationException::withMessages([
                'shipment' => 'Data proposal tarif override tidak valid.',
            ]);
        }

        foreach (['subtotal_amount', 'insurance_amount', 'admin_fee', 'total_amount'] as $field) {
            if (! array_key_exists($field, $proposed)) {
                throw ValidationException::withMessages([
                    'shipment' => 'Data proposal tarif override tidak lengkap.',
                ]);
            }
        }

        DB::transaction(function () use ($shipment, $approver, $approvalNote, $task, $proposed) {
            $reason = (string) ($task->action_data['reason'] ?? 'Override tarif manual disetujui.');

            app(OperationalIssueService::class)->applyShipmentManualOverride(
                $shipment,
                $approver,
                [
                    'subtotal_amount' => (float) $proposed['subtotal_amount'],
                    'insurance_amount' => (float) $proposed['insurance_amount'],
                    'admin_fee' => (float) $proposed['admin_fee'],
                    'total_amount' => (float) $proposed['total_amount'],
                ],
                $reason
            );

            $task->complete([
                'decision' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now()->toIso8601String(),
                'approval_note' => $approvalNote,
                'applied_amounts' => $proposed,
            ]);

            if ($approvalNote) {
                $task->update([
                    'notes' => trim(($task->notes ? $task->notes.' ' : '').'Approval note: '.$approvalNote),
                ]);
            }
        });

        return $shipment->fresh();
    }

    public function rejectPricingOverrideRequest(Shipment $shipment, User $approver, string $rejectionReason): Shipment
    {
        if ($approver->role !== 'admin') {
            throw ValidationException::withMessages([
                'approver' => 'Hanya admin yang dapat menolak override tarif.',
            ]);
        }

        $task = AdminTask::query()
            ->where('task_type', 'shipment_pricing_override_approval')
            ->where('status', 'pending')
            ->where('action_data->shipment_id', $shipment->id)
            ->latest()
            ->first();

        if (! $task) {
            throw ValidationException::withMessages([
                'shipment' => 'Tidak ada permintaan override tarif yang menunggu review.',
            ]);
        }

        $hasPendingFallbackReview = AdminTask::query()
            ->where('task_type', 'shipment_pricing_fallback_review')
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('action_data->shipment_id', $shipment->id)
            ->exists();

        $before = $shipment->only([
            'processing_status',
            'processing_error',
            'pricing_approval_status',
            'pricing_mode',
            'total_amount',
        ]);

        DB::transaction(function () use ($shipment, $approver, $rejectionReason, $task, $hasPendingFallbackReview, $before) {
            $shipment->forceFill([
                'processing_status' => $hasPendingFallbackReview ? 'needs_manual_review' : 'ok',
                'processing_error' => $hasPendingFallbackReview
                    ? 'Override tarif ditolak. Shipment tetap menunggu review fallback estimate.'
                    : null,
                'pricing_approval_status' => 'rejected',
                'pricing_approved_by' => null,
                'pricing_approved_at' => null,
            ])->save();

            $task->update([
                'status' => 'cancelled',
                'completed_at' => now(),
                'result' => [
                    'decision' => 'rejected',
                    'rejected_by' => $approver->id,
                    'rejected_at' => now()->toIso8601String(),
                    'reason' => $rejectionReason,
                ],
                'notes' => trim(($task->notes ? $task->notes.' ' : '').'Rejection reason: '.$rejectionReason),
            ]);

            $this->auditLogService->record(
                'shipment.pricing_override_rejected',
                $shipment,
                $approver,
                $before,
                $shipment->fresh()->only([
                    'processing_status',
                    'processing_error',
                    'pricing_approval_status',
                    'pricing_mode',
                    'total_amount',
                ]),
                $rejectionReason,
                [
                    'source' => 'user_action',
                    'is_manual_correction' => false,
                    'correction_reference' => $rejectionReason,
                ]
            );
        });

        return $shipment->fresh();
    }

    private function createFallbackPricingReviewTask(Shipment $shipment, ?User $actor, string $message): void
    {
        $existingTask = AdminTask::query()
            ->where('task_type', 'shipment_pricing_fallback_review')
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('action_data->shipment_id', $shipment->id)
            ->latest()
            ->first();

        if ($existingTask) {
            return;
        }

        $createdBy = $actor?->id ?? User::query()->whereIn('role', ['admin', 'manager'])->value('id') ?? User::query()->value('id');

        if (! $createdBy) {
            return;
        }

        AdminTask::query()->create([
            'task_type' => 'shipment_pricing_fallback_review',
            'title' => 'Review Ongkir Fallback '.$shipment->tracking_number,
            'description' => $message,
            'assigned_to' => null,
            'created_by' => $createdBy,
            'status' => 'pending',
            'priority' => 'high',
            'action_data' => [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'auto_total_amount' => (float) $shipment->auto_total_amount,
                'reason' => $message,
            ],
            'notes' => 'Shipment dibuat dengan fallback tariff dan menunggu review manual.',
        ]);
    }

    private function supportsDestinationBranchColumn(): bool
    {
        if ($this->hasDestinationBranchColumn !== null) {
            return $this->hasDestinationBranchColumn;
        }

        $this->hasDestinationBranchColumn = Schema::hasColumn('shipments', 'destination_branch_id');

        return $this->hasDestinationBranchColumn;
    }

    public function syncPaymentStatus(Shipment $shipment, string $paymentStatus): Shipment
    {
        $before = $shipment->only(['payment_status']);

        $mappedStatus = match ($paymentStatus) {
            'settlement' => 'paid',
            'pending' => 'pending',
            'refund' => 'refunded',
            default => 'failed',
        };

        $shipment->update([
            'payment_status' => $mappedStatus,
        ]);

        $this->auditLogService->record(
            'shipment.sync_payment_status',
            $shipment,
            null,
            $before,
            $shipment->fresh()->only(['payment_status']),
            'Status payment disinkronkan.'
        );

        if (($before['payment_status'] ?? null) !== 'failed' && $mappedStatus === 'failed') {
            $this->notificationService->notifyShipmentCustomer(
                $shipment->fresh(['customer.user']),
                'payment_failed',
                'Pembayaran Gagal',
                sprintf('Pembayaran untuk shipment %s berstatus gagal. Mohon lakukan pengecekan ulang.', $shipment->tracking_number),
                [
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'payment_status' => $mappedStatus,
                ],
                'high'
            );
        }

        return $shipment->fresh();
    }

    public function recalculateShipmentTotals(Shipment $shipment): Shipment
    {
        $shipment->loadMissing('items', 'zone');

        $totalWeight = (float) $shipment->items->sum('weight_kg');
        $totalVolume = (float) $shipment->items->sum(function (ShipmentItem $item) {
            $length = (float) ($item->length_cm ?? 0);
            $width = (float) ($item->width_cm ?? 0);
            $height = (float) ($item->height_cm ?? 0);

            return $length * $width * $height;
        });

        $shipment->forceFill([
            'total_weight_kg' => $totalWeight > 0 ? $totalWeight : $shipment->total_weight_kg,
            'total_volume' => $totalVolume,
        ])->save();

        if ($shipment->zone_id) {
            $amount = $this->calculateTotalAmount([
                'zone_id' => $shipment->zone_id,
                'branch_id' => $shipment->branch_id,
                'total_weight_kg' => $shipment->total_weight_kg,
                'service_type' => $shipment->service_type,
                'insurance_amount' => $shipment->insurance_amount,
                'admin_fee' => $shipment->admin_fee,
            ]);

            $shipment->forceFill([
                'subtotal_amount' => $amount['subtotal_amount'],
                'total_amount' => $amount['total_amount'],
                'auto_subtotal_amount' => $amount['subtotal_amount'],
                'auto_total_amount' => $amount['total_amount'],
            ])->save();
        }

        return $shipment->fresh(['items', 'zone']);
    }
}
