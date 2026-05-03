<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Shipment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransPaymentController extends Controller
{
    public function createSnapToken(Request $request, Shipment $shipment, MidtransService $midtransService): JsonResponse
    {
        $this->authorize('view', $shipment);

        // Cari payment pending atau buat baru jika belum ada
        // We also allow retrying failed/expired/cancelled payments
        $payment = $shipment->payments()
            ->whereIn('status', ['pending', 'unpaid', 'failed', 'expire', 'cancel'])
            ->first();

        if (!$payment) {
            $payment = $shipment->payments()->create([
                'customer_id' => $shipment->customer_id,
                'amount' => $shipment->total_amount,
                'status' => 'pending',
                'method' => 'midtrans',
                'notes' => 'Payment dibuat otomatis via Snap Token request.',
            ]);
        }

        if ($payment->status === 'settlement' || $payment->status === 'paid') {
            return response()->json([
                'message' => 'Shipment ini sudah lunas.',
            ], 422);
        }

        // If it's a retry (failed/expire/cancel), we need to clear midtrans_order_id to force new transaction
        if (in_array($payment->status, ['failed', 'expire', 'cancel'], true)) {
            $payment->update(['midtrans_order_id' => null]);
        }

        $result = $midtransService->createSnapTransaction($payment, $shipment);

        $payment->update([
            'method' => 'midtrans',
            'status' => 'pending',
            'midtrans_order_id' => $result['order_id'],
            'snap_token' => $result['snap_token'],
            'snap_redirect_url' => $result['snap_redirect_url'],
            'gateway_payload' => $result['payload'],
        ]);

        return response()->json([
            'message' => 'Snap token berhasil dibuat.',
            'data' => [
                'snap_token' => $payment->snap_token,
                'snap_redirect_url' => $payment->snap_redirect_url,
                'midtrans_order_id' => $payment->midtrans_order_id,
            ],
        ]);
    }
}
