<?php

namespace Database\Factories;

use App\Models\GroupTourDestination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroupTourDestination>
 */
class GroupTourDestinationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->country();

        return [
            'slug' => fake()->unique()->slug(2),
            'name' => $name,
            'meta_description' => fake()->sentence(),
            'hero_eyebrow' => 'GROUP TOURS',
            'heading' => 'Discover '.$name,
            'intro' => fake()->sentence(),
            'hero_image' => 'images/placeholder-photo.svg',
            'states_heading' => 'Explore the regions',
            'states' => [
                ['name' => fake()->city(), 'region' => 'northern', 'capital' => fake()->city(), 'tagline' => fake()->sentence(), 'image' => '', 'alt' => ''],
            ],
            'position' => 10,
        ];
    }
}
