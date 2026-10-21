<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_stats_charts_and_tables(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::admins()->first())->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pendapatan terverifikasi bulan ini')
            ->assertSee('data-chart=', false)
            ->assertSee('Acara terdekat (lunas)')
            ->assertSee('Booking terbaru')
            ->assertSee('vendor/chartjs/chart.umd.min.js', false);
    }

    public function test_customer_dashboard_shows_countdown_and_unpaid_bills(): void
    {
        $this->seed(DatabaseSeeder::class);
        $customer = User::where('email', 'customer@restikey.com')->first();

        $this->actingAs($customer)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-countdown=', false)
            ->assertSee('Tagihan')
            ->assertSee('Pelunasan')
            ->assertSee('Cek ketersediaan tanggal');
    }
}
