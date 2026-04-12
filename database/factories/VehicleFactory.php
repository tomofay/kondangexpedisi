<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->randomElement(['Motor', 'Van', 'Truck']).' '.fake()->numberBetween(1, 99),
            'plate_number' => strtoupper(fake()->bothify('B #### ???')),
            'type' => fake()->randomElement(['motorcycle', 'car', 'van', 'truck']),
            'capacity_kg' => fake()->randomFloat(2, 20, 500),
            'status' => fake()->randomElement(['available', 'in_use', 'maintenance', 'inactive']),
        ];
    }
}
