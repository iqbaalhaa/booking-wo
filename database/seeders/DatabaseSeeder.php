<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PackageSeeder::class,
            AddonSeeder::class,
            PortfolioSeeder::class,
            BlockedDateSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
