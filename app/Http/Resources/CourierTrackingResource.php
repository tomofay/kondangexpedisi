<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourierTrackingResource extends JsonResource
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
            'status' => [
                'code' => $this->status->code,
                'name' => $this->status->name,
            ],
            'location' => $this->location,
            'notes' => $this->notes,
            'gps' => [
                'lat' => (float) $this->gps_lat,
                'lng' => (float) $this->gps_lng,
            ],
            'event_at' => $this->event_at->toIso8601String(),
        ];
    }
}
