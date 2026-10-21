<?php

namespace Tests\Feature\Admin;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockedDateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_block_a_date_range_and_existing_dates_are_skipped(): void
    {
        $start = today()->addDays(40);
        BlockedDate::create(['date' => $start->copy()->addDay(), 'reason' => 'Sudah ada']);

        $this->actingAs($this->admin)->post(route('admin.blocked-dates.store'), [
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addDays(3)->toDateString(),
            'reason' => 'Libur tim',
        ])->assertSessionHas('success', '3 tanggal berhasil diblokir. 1 tanggal sudah diblokir sebelumnya.');

        $this->assertDatabaseCount('blocked_dates', 4);
    }

    public function test_cannot_block_date_with_confirmed_or_paid_booking(): void
    {
        $date = today()->addDays(50);
        Booking::factory()->paid()->create(['event_date' => $date]);

        $this->actingAs($this->admin)->post(route('admin.blocked-dates.store'), [
            'start_date' => $date->copy()->subDay()->toDateString(),
            'end_date' => $date->copy()->addDay()->toDateString(),
        ])->assertSessionHasErrors('start_date');

        $this->assertDatabaseCount('blocked_dates', 0);
    }

    public function test_pending_booking_does_not_prevent_blocking_but_warns(): void
    {
        $date = today()->addDays(60);
        Booking::factory()->create(['event_date' => $date]);

        $this->actingAs($this->admin)->post(route('admin.blocked-dates.store'), ['start_date' => $date->toDateString()])
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('blocked_dates', 1);
    }

    public function test_past_date_and_invalid_input_are_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.blocked-dates.store'), ['start_date' => today()->subDay()->toDateString()])
            ->assertSessionHasErrors(['start_date' => 'Tanggal yang sudah lewat tidak perlu diblokir.']);

        $this->actingAs($this->admin)->post(route('admin.blocked-dates.store'), ['start_date' => 'bukan-tanggal'])
            ->assertSessionHasErrors('start_date');
    }
}
