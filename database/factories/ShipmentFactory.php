<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalAmount = fake()->numberBetween(20000, 150000);

        return [
            'tracking_number' => 'SXP-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'customer_id' => Customer::factory(),
            'branch_id' => Branch::factory(),
            'courier_id' => User::factory()->state(['role' => 'courier']),
            'vehicle_id' => Vehicle::factory(),
            'zone_id' => Zone::factory(),
            'status_id' => ShipmentStatus::factory(),
            'sender_name' => fake()->name(),
            'sender_phone' => fake()->e164PhoneNumber(),
            'sender_address' => fake()->address(),
            'recipient_name' => fake()->name(),
            'recipient_phone' => fake()->e164PhoneNumber(),
            'recipient_address' => fake()->address(),
            'service_type' => fake()->randomElement(['regular', 'express', 'same_day', 'economy']),
            'total_weight_kg' => fake()->randomFloat(2, 0.5, 15),
            'total_volume' => fake()->randomFloat(2, 1, 200),
            'subtotal_amount' => $totalAmount,
            'insurance_amount' => fake()->numberBetween(0, 5000),
            'admin_fee' => 2500,
            'total_amount' => $totalAmount + 2500,
            'is_cod' => false,
            'cod_amount' => 0,
            'payment_status' => fake()->randomElement(['unpaid', 'pending', 'paid']),
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDays(fake()->numberBetween(1, 4)),
            'notes' => fake()->sentence(),
        ];
    }
}
