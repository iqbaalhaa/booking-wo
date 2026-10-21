<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlockedDate>
 */
class BlockedDateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => today()->addDays(fake()->unique()->numberBetween(1, 365)),
            'reason' => fake()->randomElement(['Libur tim', 'Perawatan peralatan', 'Acara internal', 'Libur nasional']),
        ];
    }
}
