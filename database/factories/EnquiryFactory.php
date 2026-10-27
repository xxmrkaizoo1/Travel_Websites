<?php

namespace Database\Factories;

use App\Enums\EnquirySource;
use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '01'.fake()->numerify('########'),
            'destination' => fake()->randomElement(['Bali', 'Bangkok', 'Seoul', 'Melbourne']),
            'message' => fake()->sentence(12),
            'source' => EnquirySource::Website,
            'status' => EnquiryStatus::New,
        ];
    }

    public function status(EnquiryStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
