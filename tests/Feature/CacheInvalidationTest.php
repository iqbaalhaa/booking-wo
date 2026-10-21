<?php

namespace Tests\Feature;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Package;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cached_calendar_updates_after_booking_and_blocked_date_changes(): void
    {
        $service = app(AvailabilityService::class);
        $date = today()->addMonths(2)->startOfMonth()->addDays(9);
        $status = fn () => $service->cachedMonthStatus($date->year, $date->month)[$date->toDateString()];

        $this->assertSame('available', $status()); // sekarang ter-cache

        $booking = Booking::factory()->create(['event_date' => $date]);
        $this->assertSame('pending', $status());

        $booking->update(['status' => Booking::STATUS_CONFIRMED]);
        $this->assertSame('booked', $status());

        $booking->update(['status' => Booking::STATUS_CANCELLED]);
        BlockedDate::create(['date' => $date]);
        $this->assertSame('blocked', $status());

        BlockedDate::whereDate('date', $date)->first()->delete();
        $this->assertSame('available', $status());
    }

    public function test_package_catalog_cache_refreshes_on_package_and_feature_changes(): void
    {
        $package = Package::factory()->create(['name' => 'Gold']);
        $this->assertSame(['Gold'], Package::activeCatalog()->pluck('name')->all());

        $package->update(['name' => 'Gold Plus']);
        $this->assertSame(['Gold Plus'], Package::activeCatalog()->pluck('name')->all());

        $package->features()->create(['feature' => 'MC']);
        $this->assertSame(['MC'], Package::activeCatalog()->first()->features->pluck('feature')->all());

        $package->update(['is_active' => false]);
        $this->assertTrue(Package::activeCatalog()->isEmpty());
    }
}
