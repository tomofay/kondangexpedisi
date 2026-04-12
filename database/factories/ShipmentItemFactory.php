<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShipmentItem>
 */
class ShipmentItemFactory extends Factory
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
            'item_name' => fake()->randomElement(['Dokumen', 'Elektronik', 'Pakaian', 'Kosmetik']),
            'quantity' => fake()->numberBetween(1, 3),
            'weight_kg' => fake()->randomFloat(2, 0.1, 5),
            'length_cm' => fake()->randomFloat(2, 10, 50),
            'width_cm' => fake()->randomFloat(2, 10, 50),
            'height_cm' => fake()->randomFloat(2, 5, 30),
            'declared_value' => fake()->numberBetween(10000, 1500000),
            'description' => fake()->sentence(),
        ];
    }
}
