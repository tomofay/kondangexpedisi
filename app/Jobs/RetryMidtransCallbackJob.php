<?php

namespace App\Jobs;

use App\Models\ErrorLog;
use App\Models\Payment;
use App\Services\AuditLogService;
use App\Services\OperationalIssueService;
use App\Services\ShipmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class RetryMidtransCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(public readonly int $errorLogId)
    {
    }

    public function handle(
        OperationalIssueService $operationalIssueService,
        ShipmentService $shipmentService,
        AuditLogService $auditLogService
    ): void {
        $errorLog = ErrorLog::query()->find($this->errorLogId);

        if (! $errorLog || $errorLog->resolved_at) {
            return;
        }

        $payload = $errorLog->context['payload'] ?? null;

        if (! is_array($payload)) {
            throw new RuntimeException('Payload callback Midtrans tidak ditemukan pada error log.');
        }

        $payment = null;

        if (! empty($errorLog->context['payment_id'])) {
            $payment = Payment::query()->find((int) $errorLog->context['payment_id']);
        }

        if (! $payment && ! empty($payload['order_id'])) {
            $payment = Payment::query()->where('midtrans_order_id', (string) $payload['order_id'])->first();
        }

        if (! $payment) {
            throw new RuntimeException('Payment untuk retry callback Midtrans tidak ditemukan.');
        }

        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        $paymentStatus = match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'settlement' : 'pending',
            'settlement' => 'settlement',
            'pending' => 'pending',
            'deny' => 'deny',
            'expire' => 'expire',
            'cancel' => 'cancel',
            'refund', 'partial_refund' => 'refund',
            default => 'failed',
        };

        $before = $payment->only(['status', 'processing_status', 'processing_error']);

        DB::transaction(function () use ($payment, $payload, $paymentStatus, $shipmentService, $auditLogService, $before) {
            $payment->update([
                'status' => $paymentStatus,
                'processing_status' => 'ok',
                'processing_error' => null,
                'midtrans_transaction_id' => $payload['transaction_id'] ?? $payment->midtrans_transaction_id,
                'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
                'bank_name' => $payload['bank'] ?? $payment->bank_name,
                'va_number' => $payload['va_numbers'][0]['va_number'] ?? ($payload['permata_va_number'] ?? $payment->va_number),
                'fraud_status' => $payload['fraud_status'] ?? $payment->fraud_status,
                'signature_key' => $payload['signature_key'] ?? $payment->signature_key,
                'transaction_time' => $payload['transaction_time'] ?? $payment->transaction_time,
                'paid_at' => in_array($paymentStatus, ['settlement'], true) ? now() : $payment->paid_at,
                'gateway_payload' => $payload,
            ]);

            if ($payment->shipment) {
                $shipmentService->syncPaymentStatus($payment->shipment, $paymentStatus);
            }

            $auditLogService->record(
                'payment.midtrans_callback.retry',
                $payment,
                null,
                $before,
                $payment->fresh()->only(['status', 'processing_status', 'processing_error']),
                'Retry callback Midtrans berhasil diproses dari queue.',
                [
                    'source' => 'system_retry',
                    'is_manual_correction' => false,
                ]
            );
        });

        $operationalIssueService->recordIntegrationSuccess('midtrans');
        $operationalIssueService->resolveErrorLog($errorLog, null, 'Retry Midtrans callback berhasil.');
    }

    public function failed(Throwable $exception): void
    {
        $errorLog = ErrorLog::query()->find($this->errorLogId);

        if (! $errorLog || $errorLog->resolved_at !== null) {
            return;
        }

        app(OperationalIssueService::class)->createManualDeadLetterTask(
            $errorLog,
            sprintf('Retry callback Midtrans gagal setelah %d percobaan.', $this->tries),
            self::class,
            $exception
        );
    }
}