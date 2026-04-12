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

        $customerDetails = [
            'first_name' => $shipment->recipient_name,
            'phone' => $shipment->recipient_phone,
            'billing_address' => [
                'address' => $shipment->recipient_address,
            ],
            'shipping_address' => [
                'address' => $shipment->sender_address,
            ],
        ];

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round((float) $payment->amount),
            ],
            'customer_details' => $customerDetails,
            'item_details' => [
                [
                    'id' => $shipment->tracking_number,
                    'price' => (int) round((float) $payment->amount),
                    'quantity' => 1,
                    'name' => 'Shipment '.$shipment->tracking_number,
                ],
            ],
        ];

        $snapToken = Snap::getSnapToken($payload);

        return [
            'order_id' => $orderId,
            'snap_token' => $snapToken,
            'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$snapToken,
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
