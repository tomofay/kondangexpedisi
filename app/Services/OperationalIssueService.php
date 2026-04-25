<?php

namespace App\Services;

use App\Models\ErrorLog;
use App\Models\IntegrationStatus;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class OperationalIssueService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function recordError(string $module, string $message, array $context = [], ?User $actor = null, ?Throwable $throwable = null, string $severity = 'high'): ErrorLog
    {
        return ErrorLog::query()->create([
            'error_type' => $throwable ? 'exception' : 'warning',
            'module' => $module,
            'message' => $message,
            'stack_trace' => $throwable?->getTraceAsString(),
            'context' => $context,
            'file_name' => $throwable?->getFile(),
            'line_number' => $throwable?->getLine(),
            'user_id' => $actor?->id,
            'severity' => $severity,
        ]);
    }

    public function resolveErrorLog(ErrorLog $errorLog, ?User $actor = null, ?string $reason = null): ErrorLog
    {
        if ($errorLog->resolved_at !== null) {
            return $errorLog->fresh();
        }

        $before = $errorLog->only(['resolved_at']);

        $errorLog->resolve();

        $this->auditLogService->record(
            'error_log.resolve',
            $errorLog,
            $actor,
            $before,
            $errorLog->fresh()->only(['resolved_at']),
            $reason ?: 'Error log diselesaikan.',
            [
                'source' => $actor ? 'user_action' : 'system_automatic',
                'is_manual_correction' => $actor !== null,
                'correction_reference' => $reason ?: null,
            ]
        );

        return $errorLog->fresh();
    }

    public function recordIntegrationFailure(string $serviceName, string $message, array $context = [], ?User $actor = null, ?Throwable $throwable = null): ErrorLog
    {
        $integrationStatus = IntegrationStatus::query()->firstOrCreate(
            ['service_name' => $serviceName],
            ['status' => 'healthy', 'success_count' => 0, 'failure_count' => 0]
        );

        $integrationStatus->recordFailure($message);

        return $this->recordError('integration.'.$serviceName, $message, $context, $actor, $throwable, 'critical');
    }

    public function recordIntegrationSuccess(string $serviceName): void
    {
        $integrationStatus = IntegrationStatus::query()->firstOrCreate(
            ['service_name' => $serviceName],
            ['status' => 'healthy', 'success_count' => 0, 'failure_count' => 0]
        );

        $integrationStatus->recordSuccess();
    }

    public function markShipmentError(Shipment $shipment, string $message, array $context = [], ?User $actor = null, ?Throwable $throwable = null): Shipment
    {
        $shipment->forceFill([
            'processing_status' => 'error',
            'processing_error' => $message,
        ])->save();

        $this->recordError('shipment', $message, array_merge($context, ['shipment_id' => $shipment->id]), $actor, $throwable, 'high');

        return $shipment->fresh();
    }

    public function clearShipmentError(Shipment $shipment): Shipment
    {
        $shipment->forceFill([
            'processing_status' => 'ok',
            'processing_error' => null,
        ])->save();

        return $shipment->fresh();
    }

    public function markPaymentError(Payment $payment, string $message, array $context = [], ?User $actor = null, ?Throwable $throwable = null): Payment
    {
        $payment->forceFill([
            'processing_status' => 'error',
            'processing_error' => $message,
        ])->save();

        $this->recordError('payment', $message, array_merge($context, ['payment_id' => $payment->id]), $actor, $throwable, 'high');

        return $payment->fresh();
    }

    public function clearPaymentError(Payment $payment): Payment
    {
        $payment->forceFill([
            'processing_status' => 'ok',
            'processing_error' => null,
        ])->save();

        return $payment->fresh();
    }

    public function applyShipmentManualOverride(Shipment $shipment, User $actor, array $attributes, string $reason): Shipment
    {
        $before = $shipment->only([
            'subtotal_amount',
            'insurance_amount',
            'admin_fee',
            'total_amount',
            'processing_status',
            'pricing_mode',
        ]);

        $shipment->forceFill([
            'subtotal_amount' => $attributes['subtotal_amount'],
            'insurance_amount' => $attributes['insurance_amount'],
            'admin_fee' => $attributes['admin_fee'],
            'total_amount' => $attributes['total_amount'],
            'processing_status' => 'ok',
            'processing_error' => null,
            'pricing_mode' => 'manual',
            'manual_override_by' => $actor->id,
            'manual_override_reason' => $reason,
            'manual_override_at' => now(),
        ])->save();

        $this->auditLogService->record(
            'shipment.manual_override',
            $shipment,
            $actor,
            $before,
            $shipment->fresh()->only([
                'subtotal_amount',
                'insurance_amount',
                'admin_fee',
                'total_amount',
                'processing_status',
                'pricing_mode',
                'manual_override_by',
                'manual_override_reason',
                'manual_override_at',
            ]),
            $reason,
            [
                'source' => 'user_action',
                'is_manual_correction' => true,
                'correction_reference' => $reason,
            ]
        );

        return $shipment->fresh();
    }

    public function applyPaymentManualOverride(Payment $payment, User $actor, array $attributes, string $reason): Payment
    {
        $before = $payment->only([
            'status',
            'processing_status',
            'processing_error',
        ]);

        $payment->forceFill([
            'status' => $attributes['status'] ?? $payment->status,
            'processing_status' => 'ok',
            'processing_error' => null,
            'manual_override_by' => $actor->id,
            'manual_override_reason' => $reason,
            'manual_override_at' => now(),
        ])->save();

        $this->auditLogService->record(
            'payment.manual_override',
            $payment,
            $actor,
            $before,
            $payment->fresh()->only([
                'status',
                'processing_status',
                'processing_error',
                'manual_override_by',
                'manual_override_reason',
                'manual_override_at',
            ]),
            $reason,
            [
                'source' => 'user_action',
                'is_manual_correction' => true,
                'correction_reference' => $reason,
            ]
        );

        return $payment->fresh();
    }
}