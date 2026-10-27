<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $travelDate = fake()->dateTimeBetween('+1 week', '+6 months');

        return [
            'user_id' => User::factory(),
            'destination' => fake()->randomElement(['Bali', 'Bangkok', 'Seoul', 'Melbourne', 'Beijing', 'Da Nang']),
            'package_name' => null,
            'travel_date' => $travelDate,
            'return_date' => (clone $travelDate)->modify('+5 days'),
            'adults' => fake()->numberBetween(1, 4),
            'children' => 0,
            'amount' => fake()->numberBetween(2000, 15000),
            'status' => BookingStatus::Pending,
            'notes' => null,
        ];
    }

    public function status(BookingStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
