<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Portfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_list_shows_only_active_packages(): void
    {
        Package::factory()->create(['name' => 'Paket Aktif']);
        Package::factory()->inactive()->create(['name' => 'Paket Arsip']);

        $this->get(route('packages.index'))
            ->assertOk()
            ->assertSee('Paket Aktif')
            ->assertDontSee('Paket Arsip');
    }

    public function test_package_detail_is_found_by_slug_and_hidden_when_inactive(): void
    {
        $package = Package::factory()->create(['name' => 'Gold', 'slug' => 'gold']);
        $package->features()->create(['feature' => 'MC profesional']);
        $inactive = Package::factory()->inactive()->create();

        $this->get('/paket/gold')->assertOk()->assertSee('MC profesional')->assertSee('Booking paket ini');
        $this->get(route('packages.show', $inactive))->assertNotFound();
    }

    public function test_portfolio_gallery_filters_by_package_slug(): void
    {
        $gold = Package::factory()->create(['slug' => 'gold']);
        Portfolio::factory()->create(['title' => 'Pernikahan Gold', 'package_id' => $gold->id]);
        Portfolio::factory()->create(['title' => 'Pernikahan Lain']);

        $this->get(route('portfolios.index', ['paket' => 'gold']))
            ->assertOk()
            ->assertSee('Pernikahan Gold')
            ->assertDontSee('Pernikahan Lain');

        $this->get(route('portfolios.index'))->assertSee(['Pernikahan Gold', 'Pernikahan Lain']);
    }
}
