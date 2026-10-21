<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PackageFeature>
 */
class PackageFeatureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'package_id' => Package::factory(),
            'feature' => fake()->sentence(4),
        ];
    }
}
