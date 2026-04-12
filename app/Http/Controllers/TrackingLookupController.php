<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackingLookupController extends Controller
{
    public function show(Request $request, string $trackingNumber): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'recipient_phone' => ['required', 'string', 'min:8', 'max:20', 'regex:/^[0-9+\-\s().]+$/'],
        ], [
            'recipient_phone.required' => 'Nomor HP penerima wajib diisi.',
            'recipient_phone.regex' => 'Format nomor HP penerima tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $shipment = Shipment::query()
            ->where('tracking_number', $trackingNumber)
            ->with(['branch', 'courier', 'status', 'trackings.status'])
            ->first();

        $inputPhone = $this->normalizePhone((string) $request->input('recipient_phone'));
        $shipmentPhone = $this->normalizePhone($shipment?->recipient_phone);

        if (! $shipment || $inputPhone === '' || $shipmentPhone === '' || $inputPhone !== $shipmentPhone) {
            return response()->json([
                'message' => 'Data tidak cocok. Pastikan nomor resi dan nomor HP penerima benar.',
            ], 404);
        }

        $shipment->setRelation('trackings', $shipment->trackings->sortBy('event_at')->values());

        return response()->json([
            'message' => 'Tracking shipment ditemukan.',
            'data' => [
                'tracking_number' => $shipment->tracking_number,
                'service_type' => $shipment->service_type,
                'recipient_name' => $shipment->recipient_name,
                'recipient_phone_masked' => $this->maskPhone($shipment->recipient_phone),
                'branch' => $shipment->branch?->only(['id', 'name', 'city']),
                'courier' => $shipment->courier?->only(['id', 'name']),
                'status' => $shipment->status?->only(['id', 'code', 'name']),
                'trackings' => $shipment->trackings->map(function ($tracking) {
                    return [
                        'status' => $tracking->status?->only(['code', 'name']),
                        'location' => $tracking->location,
                        'notes' => $tracking->notes,
                        'event_at' => $tracking->event_at,
                    ];
                }),
            ],
        ]);
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (! $digits) {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    private function maskPhone(?string $phone): string
    {
        $normalized = $this->normalizePhone($phone);

        if ($normalized === '') {
            return '-';
        }

        $length = strlen($normalized);

        if ($length <= 5) {
            return str_repeat('*', $length);
        }

        return substr($normalized, 0, 4).str_repeat('*', max($length - 7, 2)).substr($normalized, -3);
    }
}
