<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourierShipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tracking_number' => $this->tracking_number,
            'status' => [
                'code' => $this->status->code,
                'name' => $this->status->name,
                'color' => $this->status->badge_color,
            ],
            'sender' => [
                'name' => $this->sender_name,
                'phone' => $this->sender_phone,
                'address' => $this->sender_address,
            ],
            'recipient' => [
                'name' => $this->recipient_name,
                'phone' => $this->recipient_phone,
                'address' => $this->recipient_address,
            ],
            'service_type' => $this->service_type,
            'total_weight_kg' => (float) $this->total_weight_kg,
            'payment' => [
                'status' => $this->payment_status,
                'total_amount' => (float) $this->total_amount,
                'is_cod' => (bool) $this->is_cod,
                'cod_amount' => (float) $this->cod_amount,
            ],
            'estimated_delivery' => $this->estimated_delivery_at?->toIso8601String(),
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
