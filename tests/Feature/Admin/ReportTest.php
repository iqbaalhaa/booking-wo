<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_booking_report_totals_follow_period_status_and_package_filters(): void
    {
        $gold = Package::factory()->create(['price' => 60_000_000]);
        $silver = Package::factory()->create(['price' => 35_000_000]);
        $month = today()->addMonths(3)->startOfMonth();

        Booking::factory()->for($gold)->confirmed()->create(['event_date' => $month->copy()->addDays(2)]);
        Booking::factory()->for($silver)->paid()->create(['event_date' => $month->copy()->addDays(5)]);
        Booking::factory()->for($gold)->cancelled()->create(['event_date' => $month->copy()->addDays(9)]);
        Booking::factory()->for($gold)->confirmed()->create(['event_date' => $month->copy()->addMonth()]); // di luar periode

        $period = ['dari' => $month->toDateString(), 'sampai' => $month->copy()->endOfMonth()->toDateString()];

        $this->actingAs($this->admin)->get(route('admin.reports.bookings', $period))
            ->assertOk()
            ->assertViewHas('count', 3)
            ->assertViewHas('total', 155_000_000)
            ->assertSee('Rp 155.000.000');

        $this->actingAs($this->admin)->get(route('admin.reports.bookings', [...$period, 'paket' => $gold->id, 'status' => 'confirmed']))
            ->assertViewHas('count', 1)
            ->assertViewHas('total', 60_000_000);
    }

    public function test_booking_report_can_use_created_at_basis(): void
    {
        $this->travelTo(Carbon::parse('2026-03-15 10:00'));
        Booking::factory()->create(['total_price' => 40_000_000, 'event_date' => '2026-09-01']);
        $this->travelBack();

        $this->actingAs($this->admin)->get(route('admin.reports.bookings', ['dari' => '2026-03-01', 'sampai' => '2026-03-31', 'basis' => 'created_at']))
            ->assertViewHas('count', 1)
            ->assertViewHas('total', 40_000_000);

        $this->actingAs($this->admin)->get(route('admin.reports.bookings', ['dari' => '2026-03-01', 'sampai' => '2026-03-31']))
            ->assertViewHas('count', 0);
    }

    public function test_payment_report_counts_only_verified_payments_in_verification_period(): void
    {
        $inPeriod = Carbon::parse('2026-05-10 14:00');

        Payment::factory()->verified()->create(['amount' => 10_000_000, 'verified_at' => $inPeriod]);
        Payment::factory()->verified()->pelunasan()->create(['amount' => 25_000_000, 'verified_at' => Carbon::parse('2026-05-31 23:30')]);
        Payment::factory()->create(['amount' => 99_000_000]); // pending
        Payment::factory()->rejected()->create(['amount' => 5_000_000, 'verified_at' => $inPeriod]);
        Payment::factory()->verified()->create(['amount' => 7_000_000, 'verified_at' => Carbon::parse('2026-06-01 00:10')]);

        $period = ['dari' => '2026-05-01', 'sampai' => '2026-05-31'];

        $this->actingAs($this->admin)->get(route('admin.reports.payments', $period))
            ->assertOk()
            ->assertViewHas('count', 2)
            ->assertViewHas('total', 35_000_000);

        $this->actingAs($this->admin)->get(route('admin.reports.payments', [...$period, 'tipe' => 'dp']))
            ->assertViewHas('total', 10_000_000);
    }

    public function test_exports_download_excel_and_pdf(): void
    {
        Payment::factory()->verified()->create(['verified_at' => now()]);
        Booking::factory()->create(['event_date' => today()->addDays(40)]);

        foreach (['bookings', 'payments'] as $report) {
            $excel = $this->actingAs($this->admin)->get(route("admin.reports.{$report}.export", ['format' => 'excel']));
            $excel->assertOk()->assertDownload();
            $this->assertStringContainsString('spreadsheetml', $excel->headers->get('content-type'));

            $pdf = $this->actingAs($this->admin)->get(route("admin.reports.{$report}.export", ['format' => 'pdf']));
            $pdf->assertOk()->assertDownload();
            $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        }

        $this->actingAs($this->admin)->get('/admin/reports/bookings/export/csv')->assertNotFound();
    }

    public function test_invalid_period_is_rejected_and_customers_cannot_open_reports(): void
    {
        $this->actingAs($this->admin)->get(route('admin.reports.bookings', ['dari' => '2026-05-10', 'sampai' => '2026-05-01']))
            ->assertSessionHasErrors('sampai');

        $this->actingAs(User::factory()->create())->get(route('admin.reports.bookings'))->assertForbidden();
    }

    public function test_monthly_series_aggregates_per_month(): void
    {
        $this->travelTo(today()->startOfMonth()->subMonth()->addDays(3));
        Booking::factory(2)->create();
        Payment::factory()->verified()->create(['amount' => 12_000_000, 'verified_at' => now()]);
        $this->travelBack();
        Booking::factory()->create();

        $series = app(ReportService::class)->monthlySeries(12);

        $this->assertCount(12, $series['labels']);
        // Payment factory juga membuat booking-nya sendiri (bulan lalu) → bulan lalu 2 + 1, bulan ini 1
        $this->assertSame([3, 1], array_slice($series['bookings'], -2));
        $this->assertSame([12_000_000, 0], array_slice($series['revenue'], -2));
    }
}
