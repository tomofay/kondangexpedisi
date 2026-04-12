<?php

namespace Database\Factories;

use App\Models\LandingPageContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LandingPageContent>
 */
class LandingPageContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section' => fake()->randomElement(['hero', 'feature', 'testimonial', 'faq', 'cta', 'contact', 'statistic']),
            'title' => fake()->sentence(4),
            'subtitle' => fake()->sentence(8),
            'content' => fake()->paragraph(),
            'image_url' => null,
            'cta_label' => fake()->randomElement(['Cek Resi', 'Kirim Sekarang', 'Hubungi Kami']),
            'cta_url' => '/tracking',
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
            'metadata' => ['highlight' => fake()->boolean()],
        ];
    }
}
