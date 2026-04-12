<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransPaymentController extends Controller
{
    public function createSnapToken(Request $request, Payment $payment, MidtransService $midtransService): JsonResponse
    {
        $this->authorize('update', $payment);

        $payment->loadMissing('shipment');

        if (! $payment->shipment) {
            return response()->json([
                'message' => 'Shipment untuk payment ini tidak ditemukan.',
            ], 422);
        }

        if ($payment->status === 'settlement') {
            return response()->json([
                'message' => 'Payment ini sudah settlement.',
            ], 422);
        }

        $result = $midtransService->createSnapTransaction($payment, $payment->shipment);

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
