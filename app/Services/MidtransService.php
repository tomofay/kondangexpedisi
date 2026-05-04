<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Shipment;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = (string) config('services.midtrans.server_key');
        Config::$clientKey = (string) config('services.midtrans.client_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    public function createSnapTransaction(Payment $payment, Shipment $shipment): array
    {
        $orderId = $payment->midtrans_order_id ?: $this->generateOrderId($payment);

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round((float) $payment->amount),
            ],
            'customer_details' => [
                'first_name' => $shipment->sender_name,
                'email' => auth()->user()->email ?? 'customer@ekspedisi.com',
                'phone' => $shipment->sender_phone,
                'shipping_address' => [
                    'address' => $shipment->sender_address,
                ],
            ],
            'item_details' => [
                [
                    'id' => $shipment->tracking_number,
                    'price' => (int) round((float) $payment->amount),
                    'quantity' => 1,
                    'name' => 'Shipment '.$shipment->tracking_number,
                ],
            ],
            'callbacks' => [
                'finish' => route('payments.midtrans.finish'),
                'unfinish' => route('payments.midtrans.unfinish'),
                'error' => route('payments.midtrans.error'),
            ],
        ];

        try {
            \Illuminate\Support\Facades\Log::debug('Requesting Snap Transaction for Order: ' . $orderId, $payload);
            $response = \Midtrans\Snap::createTransaction($payload);
            $snapToken = $response->token;
            $redirectUrl = $response->redirect_url;
            \Illuminate\Support\Facades\Log::debug('Received Snap Token: ' . $snapToken);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Midtrans Snap::createTransaction failed: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'payload' => $payload,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return [
            'order_id' => $orderId,
            'snap_token' => $snapToken,
            'snap_redirect_url' => $redirectUrl,
            'payload' => $payload,
        ];
    }

    public function verifySignature(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $incomingSignature = (string) ($payload['signature_key'] ?? '');
        $serverKey = (string) config('services.midtrans.server_key');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $incomingSignature === '' || $serverKey === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $incomingSignature);
    }

    private function generateOrderId(Payment $payment): string
    {
        return 'PAY-'.$payment->id.'-'.now()->format('YmdHis');
    }
}
