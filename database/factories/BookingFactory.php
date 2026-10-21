<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Booking tanpa add-on: total_price = package_price.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $minDays = config('restikey.min_booking_days');

        return [
            'booking_code' => fn () => Booking::generateCode(),
            'user_id' => User::factory(),
            'package_id' => Package::factory(),
            'event_date' => today()->addDays(fake()->numberBetween($minDays + 1, $minDays + 300)),
            'event_location' => fake()->randomElement(['Gedung ', 'Hotel ', 'Ballroom ']).fake()->company().', '.fake()->city(),
            'groom_name' => fake()->firstNameMale().' '.fake()->lastName(),
            'bride_name' => fake()->firstNameFemale().' '.fake()->lastName(),
            'guest_count' => fn (array $attributes) => min(fake()->numberBetween(100, 1000), Package::find($attributes['package_id'])->max_guests),
            'notes' => fake()->optional()->sentence(),
            'package_price' => fn (array $attributes) => Package::find($attributes['package_id'])->price,
            'addons_total' => 0,
            'total_price' => fn (array $attributes) => $attributes['package_price'] + $attributes['addons_total'],
            'status' => Booking::STATUS_PENDING,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_PAID,
            'confirmed_at' => now()->subDays(10),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_COMPLETED,
            'event_date' => today()->subDays(fake()->numberBetween(1, 90)),
            'confirmed_at' => now()->subDays(120),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_CANCELLED,
            'cancel_reason' => 'Dibatalkan oleh customer.',
        ]);
    }
}
