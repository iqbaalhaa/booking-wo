<?php

namespace Tests\Feature;

use App\Mail\BookingCancelled;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AutoCancelCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancels_only_overdue_confirmed_bookings_without_dp(): void
    {
        Mail::fake();
        $days = config('restikey.auto_cancel_days');
        $overdue = now()->subDays($days)->subHour();

        $noDp = Booking::factory()->confirmed()->create(['confirmed_at' => $overdue]);
        $dpVerified = Booking::factory()->confirmed()->create(['confirmed_at' => $overdue]);
        Payment::factory()->verified()->create(['booking_id' => $dpVerified->id]);
        $dpWaiting = Booking::factory()->confirmed()->create(['confirmed_at' => $overdue]);
        Payment::factory()->create(['booking_id' => $dpWaiting->id]);
        $dpRejected = Booking::factory()->confirmed()->create(['confirmed_at' => $overdue]);
        Payment::factory()->rejected()->create(['booking_id' => $dpRejected->id]);
        $recent = Booking::factory()->confirmed()->create(['confirmed_at' => now()->subDays($days - 1)]);
        $pending = Booking::factory()->create();

        $this->artisan('bookings:auto-cancel')->expectsOutput('2 booking dibatalkan otomatis.')->assertSuccessful();

        $this->assertSame(Booking::STATUS_CANCELLED, $noDp->fresh()->status);
        $this->assertSame(Booking::STATUS_CANCELLED, $dpRejected->fresh()->status);
        $this->assertStringContainsString('Dibatalkan otomatis', $noDp->fresh()->cancel_reason);

        foreach ([$dpVerified, $dpWaiting, $recent] as $kept) {
            $this->assertSame(Booking::STATUS_CONFIRMED, $kept->fresh()->status);
        }
        $this->assertSame(Booking::STATUS_PENDING, $pending->fresh()->status);

        Mail::assertQueued(BookingCancelled::class, 2);
    }
}
