<?php

namespace Database\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Zone>
 */
class ZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(6)),
            'name' => fake()->city().' Zone',
            'description' => fake()->sentence(),
            'multiplier' => fake()->randomFloat(2, 1, 3),
            'is_active' => true,
        ];
    }
}
