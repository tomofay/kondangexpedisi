<?php

namespace App\Http\Controllers;

use App\Jobs\RetryMidtransCallbackJob;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Services\AuditLogService;
use App\Services\OperationalIssueService;
use App\Services\ShipmentService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtransService, ShipmentService $shipmentService, AuditLogService $auditLogService, OperationalIssueService $operationalIssueService): JsonResponse
    {
        $payload = $request->all();
        $orderId = (string) ($payload['order_id'] ?? '');

        if ($orderId === '') {
            $this->recordCallbackFailure($payload, 'order_id wajib diisi.', null, $operationalIssueService);

            return response()->json(['message' => 'order_id wajib diisi.'], 422);
        }

        $payment = Payment::query()->where('midtrans_order_id', $orderId)->first();

        if (! $payment) {
            $this->recordCallbackFailure($payload, 'Payment tidak ditemukan.', null, $operationalIssueService);

            return response()->json(['message' => 'Payment tidak ditemukan.'], 404);
        }

        if (! $midtransService->verifySignature($payload)) {
            $operationalIssueService->markPaymentError($payment, 'Signature Midtrans tidak valid.', ['payload' => $payload]);
            $this->recordCallbackFailure($payload, 'Signature Midtrans tidak valid.', $payment, $operationalIssueService);

            return response()->json(['message' => 'Signature Midtrans tidak valid.'], 403);
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

        $before = $payment->only(['status', 'midtrans_transaction_id', 'payment_type', 'bank_name', 'va_number', 'fraud_status']);

        try {
            DB::transaction(function () use ($payment, $paymentStatus, $payload, $shipmentService, $auditLogService, $before) {
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

                $auditLogService->record(
                    'payment.midtrans_callback',
                    $payment,
                    null,
                    $before,
                    $payment->fresh()->only(['status', 'midtrans_transaction_id', 'payment_type', 'bank_name', 'va_number', 'fraud_status', 'processing_status']),
                    'Callback Midtrans diproses.'
                );

                if ($payment->shipment) {
                    $shipmentService->syncPaymentStatus($payment->shipment, $paymentStatus);
                }
            });

            $operationalIssueService->recordIntegrationSuccess('midtrans');
        } catch (\Throwable $throwable) {
            $integrationErrorLog = $operationalIssueService->recordIntegrationFailure('midtrans', 'Callback Midtrans gagal diproses.', ['payload' => $payload, 'payment_id' => $payment->id], null, $throwable);
            $operationalIssueService->markPaymentError($payment, 'Callback Midtrans gagal diproses.', ['payload' => $payload], null, $throwable);
            RetryMidtransCallbackJob::dispatch($integrationErrorLog->id);

            return response()->json([
                'message' => 'Callback Midtrans gagal diproses.',
            ], 500);
        }

        return response()->json([
            'message' => 'Callback Midtrans berhasil diproses.',
            'status' => $paymentStatus,
        ]);
    }

    private function recordCallbackFailure(array $payload, string $notes, ?Payment $payment, OperationalIssueService $operationalIssueService): void
    {
        AuditLog::query()->create([
            'action' => 'payment.midtrans_callback_failed',
            'subject_type' => Payment::class,
            'subject_id' => $payment?->id ?? 0,
            'actor_id' => null,
            'before_state' => null,
            'after_state' => [
                'payload' => $payload,
            ],
            'notes' => $notes,
        ]);

        $operationalIssueService->recordIntegrationFailure('midtrans', $notes, ['payload' => $payload, 'payment_id' => $payment?->id], null);
    }
}
