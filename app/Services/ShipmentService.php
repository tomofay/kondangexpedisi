<?php

namespace App\Services;

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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public function __construct(private readonly AuditLogService $auditLogService)
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
            throw ValidationException::withMessages([
                'zone_id' => 'Rate card untuk rute zona asal/tujuan dan service ini belum tersedia.',
            ]);
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
        ];
    }

    public function assignCourier(Shipment $shipment, ?int $courierId, ?int $vehicleId, ?int $actorId = null): Shipment
    {
        $before = $shipment->only(['courier_id', 'vehicle_id']);
        $shipment->loadMissing('branch');

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

        return $shipment->fresh(['branch', 'courier', 'vehicle', 'status']);
    }

    public function transitionStatus(Shipment $shipment, string $statusCode, ?int $actorId = null, ?string $location = null, ?string $notes = null): Shipment
    {
        $status = ShipmentStatus::query()->where('code', $statusCode)->first();

        if (! $status) {
            throw ValidationException::withMessages([
                'status_code' => 'Status shipment tidak valid.',
            ]);
        }

        $finalCodes = ['delivered', 'cancelled', 'returned'];
        if ($shipment->status?->is_final && ! in_array($statusCode, $finalCodes, true)) {
            throw ValidationException::withMessages([
                'status_code' => 'Shipment yang sudah final tidak bisa dipindahkan ke status lain.',
            ]);
        }

        $before = $shipment->only(['status_id', 'current_status_at', 'delivered_at']);

        $shipment->update([
            'status_id' => $status->id,
            'current_status_at' => now(),
            'delivered_at' => $statusCode === 'delivered' ? now() : $shipment->delivered_at,
        ]);

        $shipment->trackings()->create([
            'status_id' => $status->id,
            'created_by' => $actorId,
            'location' => $location,
            'notes' => $notes ?: 'Status shipment diperbarui menjadi '.$status->name,
            'event_at' => now(),
        ]);

        $this->auditLogService->record(
            'shipment.transition_status',
            $shipment,
            User::query()->find($actorId),
            $before,
            $shipment->fresh()->only(['status_id', 'current_status_at', 'delivered_at']),
            $notes
        );

        return $shipment->fresh(['status', 'trackings.status']);
    }

    public function createShipment(array $data, ?User $actor = null): Shipment
    {
        $amount = $this->calculateTotalAmount($data);
        $pendingStatusId = ShipmentStatus::query()->where('code', 'pending')->value('id');

        $shipment = Shipment::query()->create([
            'tracking_number' => $this->generateTrackingNumber((int) $data['branch_id']),
            'customer_id' => $data['customer_id'] ?? null,
            'branch_id' => $data['branch_id'],
            'destination_branch_id' => $data['destination_branch_id'] ?? null,
            'courier_id' => $data['courier_id'] ?? null,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'zone_id' => $data['zone_id'],
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
            'is_cod' => (bool) ($data['is_cod'] ?? false),
            'cod_amount' => $data['cod_amount'] ?? 0,
            'payment_status' => 'pending',
            'current_status_at' => now(),
            'estimated_delivery_at' => $data['estimated_delivery_at'] ?? now()->addDays(2),
            'delivered_at' => null,
            'notes' => $data['notes'] ?? null,
        ]);

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

        return $shipment->fresh(['branch', 'status', 'trackings', 'payments']);
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
            ])->save();
        }

        return $shipment->fresh(['items', 'zone']);
    }
}
