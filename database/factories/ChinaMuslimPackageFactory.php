<?php

namespace Database\Factories;

use App\Models\ChinaMuslimPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChinaMuslimPackage>
 */
class ChinaMuslimPackageFactory extends Factory
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
            'title' => strtoupper(fake()->words(5, true)).' MUSLIM TOURS',
            'image_path' => 'china-muslim/'.fake()->lexify('????????').'.jpg',
            'price' => fake()->numberBetween(0, 9000),
            'days' => 8,
            'nights' => 7,
            'is_hot' => false,
            'link' => null,
        ];
    }
}
