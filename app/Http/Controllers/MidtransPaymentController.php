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
        try {
            $this->authorize('view', $shipment);

            // Cari payment pending atau buat baru jika belum ada
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

            // Always clear midtrans_order_id to force a fresh transaction if they click pay again
            // This avoids "order_id already used" errors in Midtrans Sandbox
            $payment->update(['midtrans_order_id' => null]);

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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Midtrans Snap Token Error: ' . $e->getMessage(), [
                'shipment_id' => $shipment->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Gagal menyiapkan pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        \Illuminate\Support\Facades\Log::debug('Midtrans Finish Callback Received', ['order_id' => $orderId, 'all' => $request->all()]);
        
        $payment = \App\Models\Payment::where('midtrans_order_id', $orderId)->first();
        
        if ($payment && $payment->shipment_id) {
            if (auth()->user() && auth()->user()->role === 'customer') {
                return redirect()->route('customer.shipments.track', $payment->shipment_id)->with('success', 'Pembayaran berhasil dikonfirmasi!');
            }
        }

        $route = auth()->user() && auth()->user()->role === 'customer' ? 'customer.dashboard' : 'dashboard';
        return redirect()->route($route)->with('success', 'Pembayaran berhasil diproses!');
    }

    public function unfinish(Request $request)
    {
        $orderId = $request->get('order_id');
        $payment = \App\Models\Payment::where('midtrans_order_id', $orderId)->first();
        
        if ($payment && $payment->shipment_id) {
            if (auth()->user() && auth()->user()->role === 'customer') {
                return redirect()->route('customer.shipments.track', $payment->shipment_id)->with('warning', 'Pembayaran belum diselesaikan.');
            }
        }

        $route = auth()->user() && auth()->user()->role === 'customer' ? 'customer.dashboard' : 'dashboard';
        return redirect()->route($route)->with('warning', 'Pembayaran belum diselesaikan atau dibatalkan.');
    }

    public function error(Request $request)
    {
        $orderId = $request->get('order_id');
        $payment = \App\Models\Payment::where('midtrans_order_id', $orderId)->first();
        
        if ($payment && $payment->shipment_id) {
            if (auth()->user() && auth()->user()->role === 'customer') {
                return redirect()->route('customer.shipments.track', $payment->shipment_id)->with('error', 'Terjadi kesalahan saat memproses pembayaran.');
            }
        }

        $route = auth()->user() && auth()->user()->role === 'customer' ? 'customer.dashboard' : 'dashboard';
        return redirect()->route($route)->with('error', 'Terjadi kesalahan saat memproses pembayaran Midtrans.');
    }
}
