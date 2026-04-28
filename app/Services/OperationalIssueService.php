<?php

namespace App\Services;

use App\Models\AdminTask;
use App\Models\ErrorLog;
use App\Models\IntegrationStatus;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class OperationalIssueService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService
    )
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

        $this->notificationService->notifyAdmins(
            'shipment_error',
            'Shipment Error Perlu Tindakan',
            sprintf('Shipment %s mengalami error: %s', $shipment->tracking_number, $message),
            [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'processing_status' => 'error',
                'context' => $context,
            ],
            'high'
        );

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

        if ($payment->shipment) {
            $this->notificationService->notifyShipmentCustomer(
                $payment->shipment,
                'payment_failed',
                'Pembayaran Gagal Diproses',
                sprintf('Pembayaran untuk shipment %s gagal diproses. Silakan cek metode pembayaran Anda.', $payment->shipment->tracking_number),
                [
                    'shipment_id' => $payment->shipment->id,
                    'tracking_number' => $payment->shipment->tracking_number,
                    'payment_id' => $payment->id,
                    'payment_status' => $payment->status,
                    'processing_status' => 'error',
                ],
                'high'
            );
        }

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
                'corrected_total_amount' => $attributes['total_amount'],
            'processing_status' => 'ok',
            'processing_error' => null,
            'pricing_mode' => 'manual',
                'pricing_approval_status' => 'approved',
                'pricing_approved_by' => $actor->id,
                'pricing_approved_at' => now(),
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

    public function createManualDeadLetterTask(
        ErrorLog $errorLog,
        string $reason,
        ?string $jobClass = null,
        ?Throwable $throwable = null,
        ?User $actor = null
    ): ?AdminTask {
        if ($errorLog->resolved_at !== null) {
            return null;
        }

        $existingTask = AdminTask::query()
            ->where('task_type', 'retry_dead_letter')
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('action_data->error_log_id', $errorLog->id)
            ->latest()
            ->first();

        if ($existingTask) {
            return $existingTask;
        }

        $createdBy = $this->resolveAdminTaskCreator($errorLog);

        if (! $createdBy) {
            Log::warning('Gagal membuat manual dead-letter task karena tidak ada user pembuat.', [
                'error_log_id' => $errorLog->id,
                'reason' => $reason,
            ]);

            return null;
        }

        $task = AdminTask::query()->create([
            'task_type' => 'retry_dead_letter',
            'title' => sprintf('Manual Follow-up: %s', $errorLog->module),
            'description' => $reason,
            'assigned_to' => null,
            'created_by' => $createdBy->id,
            'status' => 'pending',
            'priority' => $errorLog->severity === 'critical' ? 'high' : 'medium',
            'action_data' => [
                'error_log_id' => $errorLog->id,
                'module' => $errorLog->module,
                'error_type' => $errorLog->error_type,
                'severity' => $errorLog->severity,
                'retry_exhausted_at' => now()->toIso8601String(),
                'job_class' => $jobClass,
                'context' => is_array($errorLog->context) ? Arr::only($errorLog->context, [
                    'shipment_id',
                    'payment_id',
                    'tracking_id',
                    'operation',
                    'order_id',
                ]) : [],
                'last_exception' => $throwable?->getMessage(),
            ],
            'notes' => 'Task dibuat otomatis dari mekanisme dead-letter retry.',
        ]);

        $this->auditLogService->record(
            'error_log.escalate_dead_letter',
            $errorLog,
            $actor,
            [],
            [
                'manual_task_id' => $task->id,
                'manual_task_status' => $task->status,
            ],
            $reason,
            [
                'source' => $actor ? 'user_action' : 'system_automatic',
                'is_manual_correction' => $actor !== null,
                'correction_reference' => $reason,
            ]
        );

        return $task;
    }

    private function resolveAdminTaskCreator(ErrorLog $errorLog): ?User
    {
        if ($errorLog->user_id) {
            $errorActor = User::query()->find($errorLog->user_id);

            if ($errorActor) {
                return $errorActor;
            }
        }

        $adminOrManager = User::query()
            ->whereIn('role', ['admin', 'manager'])
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->first();

        if ($adminOrManager) {
            return $adminOrManager;
        }

        return User::query()->first();
    }
}