<?php

namespace Database\Factories;

use App\Models\ShipmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends Factory<ShipmentStatus>
 */
class ShipmentStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = Arr::random(config('expedition.shipment_statuses', ['pending']));

        return [
            'code' => $code.'-'.fake()->unique()->numerify('##'),
            'name' => str($code)->replace('_', ' ')->title()->value(),
            'sequence' => fake()->numberBetween(1, 10),
            'is_final' => in_array($code, ['delivered', 'cancelled', 'returned'], true),
            'badge_color' => fake()->randomElement(['amber', 'blue', 'green', 'red', 'orange']),
        ];
    }
}
