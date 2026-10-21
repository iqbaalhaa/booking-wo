<?php

namespace Tests\Feature\Admin;

use App\Mail\BookingCancelled;
use App\Mail\BookingConfirmed;
use App\Mail\BookingCreated;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use App\Services\BookingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_confirming_one_booking_cancels_other_pending_bookings_on_same_date(): void
    {
        $date = today()->addDays(80);
        [$chosen, $other] = Booking::factory(2)->create(['event_date' => $date]);
        $otherDay = Booking::factory()->create(['event_date' => $date->copy()->addDay()]);

        $this->actingAs($this->admin)->post(route('admin.bookings.confirm', $chosen))->assertSessionHas('success');

        $this->assertSame(Booking::STATUS_CONFIRMED, $chosen->fresh()->status);
        $this->assertSame(Booking::STATUS_CANCELLED, $other->fresh()->status);
        $this->assertSame(BookingService::DATE_TAKEN_REASON, $other->fresh()->cancel_reason);
        $this->assertSame(Booking::STATUS_PENDING, $otherDay->fresh()->status);

        Mail::assertQueued(BookingConfirmed::class, fn ($mail) => $mail->booking->is($chosen) && $mail->hasTo($chosen->user->email));
        Mail::assertQueued(BookingCancelled::class, fn ($mail) => $mail->booking->is($other) && $mail->hasTo($other->user->email));
    }

    public function test_cannot_confirm_when_date_already_taken(): void
    {
        $date = today()->addDays(80);
        Booking::factory()->paid()->create(['event_date' => $date]);
        $pending = Booking::factory()->create(['event_date' => $date]);

        $this->actingAs($this->admin)->post(route('admin.bookings.confirm', $pending))
            ->assertSessionHasErrors(['booking' => 'Tanggal ini sudah dikonfirmasi untuk booking lain.']);

        $this->assertSame(Booking::STATUS_PENDING, $pending->fresh()->status);
    }

    public function test_admin_can_reject_booking_with_reason_and_customer_is_emailed(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.bookings.cancel', $booking), ['cancel_reason' => 'Tim penuh di tanggal ini'])
            ->assertSessionHas('success');

        $this->assertSame('Tim penuh di tanggal ini', $booking->fresh()->cancel_reason);
        Mail::assertQueued(BookingCancelled::class);
    }

    public function test_complete_only_for_paid_booking_after_event_date(): void
    {
        $future = Booking::factory()->paid()->create(['event_date' => today()->addDays(40)]);
        $this->actingAs($this->admin)->post(route('admin.bookings.complete', $future))->assertSessionHasErrors('booking');

        $confirmedPast = Booking::factory()->confirmed()->create(['event_date' => today()->subDay()]);
        $this->actingAs($this->admin)->post(route('admin.bookings.complete', $confirmedPast))->assertSessionHasErrors('booking');

        $paidPast = Booking::factory()->paid()->create(['event_date' => today()->subDay()]);
        $this->actingAs($this->admin)->post(route('admin.bookings.complete', $paidPast))->assertSessionHas('success');

        $this->assertSame(Booking::STATUS_COMPLETED, $paidPast->fresh()->status);
        $this->assertNotNull($paidPast->fresh()->completed_at);
    }

    public function test_new_booking_notifies_customer_and_admin(): void
    {
        $customer = User::factory()->create();
        $package = Package::factory()->create();

        $this->actingAs($customer)->post(route('bookings.store'), [
            'package_id' => $package->id,
            'event_date' => today()->addDays(60)->toDateString(),
            'event_location' => 'Bandung',
            'groom_name' => 'A',
            'bride_name' => 'B',
            'guest_count' => 100,
        ])->assertSessionHasNoErrors();

        Mail::assertQueued(BookingCreated::class, fn ($mail) => ! $mail->forAdmin && $mail->hasTo($customer->email));
        Mail::assertQueued(BookingCreated::class, fn ($mail) => $mail->forAdmin && $mail->hasTo($this->admin->email));
    }

    public function test_index_filters_and_detail_pages_render(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::admins()->first();
        $pending = Booking::pending()->first();

        $this->actingAs($admin)->get(route('admin.bookings.index', ['status' => 'pending']))
            ->assertOk()->assertSee($pending->booking_code)->assertDontSee(Booking::paid()->first()->booking_code);

        $this->actingAs($admin)->get(route('admin.bookings.index', ['q' => $pending->groom_name]))
            ->assertOk()->assertSee($pending->booking_code);

        $this->actingAs($admin)->get(route('admin.bookings.index', [
            'dari' => today()->addYears(5)->toDateString(),
        ]))->assertOk()->assertSee('Tidak ada booking yang cocok');

        foreach (Booking::all() as $booking) {
            $this->actingAs($admin)->get(route('admin.bookings.show', $booking))->assertOk()->assertSee($booking->groom_name);
        }

        $this->actingAs($admin)->get(route('admin.payments.index'))
            ->assertOk()->assertSee(Payment::pending()->first()->booking->booking_code);
        $this->actingAs($admin)->get(route('admin.payments.index', ['status' => 'semua']))->assertOk();
    }
}
