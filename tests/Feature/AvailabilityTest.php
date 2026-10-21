<?php

namespace Tests\Feature;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_month_status_marks_each_date_correctly(): void
    {
        $month = today()->addMonths(3)->startOfMonth();
        $booked = $month->copy()->addDays(4);
        $pending = $month->copy()->addDays(5);
        $blocked = $month->copy()->addDays(6);

        Booking::factory()->paid()->create(['event_date' => $booked]);
        Booking::factory()->create(['event_date' => $pending]);
        Booking::factory()->cancelled()->create(['event_date' => $month]); // dibatalkan tidak mengunci
        BlockedDate::create(['date' => $blocked]);

        $statuses = app(AvailabilityService::class)->getMonthStatus($month->year, $month->month);

        $this->assertCount($month->daysInMonth, $statuses);
        $this->assertSame('booked', $statuses[$booked->toDateString()]);
        $this->assertSame('pending', $statuses[$pending->toDateString()]);
        $this->assertSame('blocked', $statuses[$blocked->toDateString()]);
        $this->assertSame('available', $statuses[$month->toDateString()]);
    }

    public function test_dates_before_minimum_booking_days_are_past(): void
    {
        $service = app(AvailabilityService::class);
        $earliest = today()->addDays(config('restikey.min_booking_days'));

        $this->assertSame('past', $service->statusOf(today()));
        $this->assertSame('past', $service->statusOf($earliest->copy()->subDay()));
        $this->assertTrue($service->isAvailable($earliest));
    }

    public function test_json_endpoint_returns_month_statuses_and_validates_format(): void
    {
        $month = today()->addMonths(2)->format('Y-m');

        $this->getJson(route('availability.json', ['month' => $month]))
            ->assertOk()
            ->assertJsonPath('month', $month)
            ->assertJsonPath('dates.'.$month.'-15', 'available');

        $this->getJson(route('availability.json', ['month' => '2026-13']))->assertStatus(422);
    }

    public function test_calendar_page_renders_and_links_available_dates_to_booking_form(): void
    {
        $month = today()->addMonths(2);

        $this->get(route('availability.index', ['bulan' => $month->format('Y-m')]))
            ->assertOk()
            ->assertSee($month->locale('id')->isoFormat('MMMM YYYY'))
            ->assertSee(route('bookings.create', ['date' => $month->copy()->day(15)->toDateString()]), false);

        // bulan di luar jangkauan dibatasi, bukan error
        $this->get(route('availability.index', ['bulan' => '1999-01']))->assertOk();
    }
}
