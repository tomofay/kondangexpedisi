<?php

namespace Database\Factories;

use App\Models\RateCard;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RateCard>
 */
class RateCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = fake()->randomFloat(2, 0, 4);
        $max = $min + fake()->randomFloat(2, 1, 5);

        return [
            'zone_id' => Zone::factory(),
            'service_type' => fake()->randomElement(['regular', 'express', 'same_day', 'economy']),
            'min_weight_kg' => $min,
            'max_weight_kg' => $max,
            'base_price' => fake()->numberBetween(15000, 60000),
            'per_kg_price' => fake()->numberBetween(3000, 10000),
            'insurance_fee' => fake()->numberBetween(1000, 5000),
            'is_active' => true,
        ];
    }
}
