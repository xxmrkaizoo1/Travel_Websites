<?php

namespace Database\Factories;

use App\Models\HomepageConsultant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageConsultant>
 */
class HomepageConsultantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position' => fake()->numberBetween(1, 50),
            'name' => fake()->firstName(),
            'role' => 'Travel Consultant',
            'whatsapp' => null,
            'photo_path' => null,
        ];
    }
}
