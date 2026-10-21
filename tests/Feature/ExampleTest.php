<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_empty_database(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Paket sedang disiapkan');
    }

    public function test_landing_page_shows_packages_addons_and_published_reviews(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee(['Silver', 'Gold', 'Platinum', 'Rp 35.000.000', 'Photobooth', 'Kata mereka'])
            ->assertSee('Bayar DP '.config('restikey.dp_percentage').'%');
    }
}
