<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTracking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShipmentTracking>
 */
class ShipmentTrackingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipment_id' => Shipment::factory(),
            'status_id' => ShipmentStatus::factory(),
            'created_by' => User::factory()->state(['role' => 'courier']),
            'location' => fake()->city(),
            'notes' => fake()->sentence(),
            'event_at' => now()->subMinutes(fake()->numberBetween(1, 720)),
        ];
    }
}
