<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Portfolio>
 */
class PortfolioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Pernikahan '.fake()->firstNameMale().' & '.fake()->firstNameFemale(),
            'description' => fake()->paragraph(),
            // shortcut: gambar demo dari URL eksternal; data asli diunggah ke storage public lewat admin
            'image' => 'https://picsum.photos/seed/'.fake()->unique()->uuid().'/1200/800',
            'event_date' => fake()->dateTimeBetween('-2 years', '-1 week'),
            'package_id' => null,
        ];
    }
}
