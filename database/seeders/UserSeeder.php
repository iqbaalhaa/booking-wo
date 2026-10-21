<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Restikey',
            'email' => 'admin@restikey.com',
        ]);

        // Customer demo dengan login tetap, sisanya acak
        User::factory()->create([
            'name' => 'Rina Customer',
            'email' => 'customer@restikey.com',
        ]);

        User::factory(4)->create();
    }
}
