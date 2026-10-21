<?php

namespace Tests\Feature\Admin;

use App\Models\Addon;
use App\Models\Package;
use App\Models\Portfolio;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_master_data_pages_render_with_seeded_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::admins()->first());

        $pages = [
            route('admin.packages.index') => 'Platinum',
            route('admin.packages.index', ['q' => 'gold']) => 'Gold',
            route('admin.packages.create') => 'Tambah fitur',
            route('admin.packages.edit', Package::first()) => 'Simpan',
            route('admin.addons.index') => 'Photobooth',
            route('admin.addons.create') => 'Nama add-on',
            route('admin.addons.edit', Addon::first()) => 'Simpan',
            route('admin.portfolios.index') => 'Pernikahan',
            route('admin.portfolios.create') => 'Tanpa paket',
            route('admin.portfolios.edit', Portfolio::first()) => 'Kosongkan jika tidak ingin mengganti foto',
            route('admin.blocked-dates.index') => 'Libur tim',
            route('admin.blocked-dates.index', ['lewat' => 1]) => 'Semua tanggal diblokir',
            route('packages.index') => 'Lihat detail paket Gold',
            route('packages.show', Package::where('slug', 'platinum')->first()) => 'Kata pasangan yang memilih paket ini',
            route('portfolios.index') => 'lightbox',
        ];

        foreach ($pages as $url => $text) {
            $this->get($url)->assertOk()->assertSee($text);
        }
    }
}
