<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Portfolio;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $packageIds = Package::pluck('id');

        // 6 portofolio terkait paket, 2 tanpa paket
        Portfolio::factory(6)->sequence(fn ($sequence) => [
            'package_id' => $packageIds[$sequence->index % $packageIds->count()],
        ])->create();

        Portfolio::factory(2)->create();
    }
}
