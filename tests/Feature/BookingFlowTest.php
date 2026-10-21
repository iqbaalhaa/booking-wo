<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Package $package;
    private Addon $mc;
    private Addon $catering;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->customer = User::factory()->create();
        $this->package = Package::factory()->create(['price' => 50_000_000, 'max_guests' => 500]);
        $this->mc = Addon::factory()->create(['name' => 'MC', 'price' => 3_000_000]);
        $this->catering = Addon::factory()->create(['name' => 'Katering', 'price' => 6_000_000]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'package_id' => $this->package->id,
            'event_date' => today()->addDays(config('restikey.min_booking_days') + 10)->toDateString(),
            'event_location' => 'Hotel Mulia, Jakarta',
            'groom_name' => 'Bagas',
            'bride_name' => 'Rina',
            'guest_count' => 300,
            'addons' => [
                $this->mc->id => ['selected' => '1', 'quantity' => '2'],
                $this->catering->id => ['quantity' => '3'], // tidak dicentang → diabaikan
            ],
        ], $overrides);
    }

    public function test_booking_is_created_with_correct_total_and_price_snapshot(): void
    {
        $response = $this->actingAs($this->customer)->post(route('bookings.store'), $this->payload());

        $booking = Booking::with('bookingAddons')->sole();
        $response->assertRedirect(route('bookings.show', $booking))->assertSessionHas('success');

        $this->assertMatchesRegularExpression('/^RSK-\d{8}-[A-Z0-9]{4}$/', $booking->booking_code);
        $this->assertSame(Booking::STATUS_PENDING, $booking->status);
        $this->assertSame(50_000_000, $booking->package_price);
        $this->assertSame(6_000_000, $booking->addons_total);
        $this->assertSame(56_000_000, $booking->total_price);
        $this->assertSame(16_800_000, $booking->minimumDp());

        $this->assertCount(1, $booking->bookingAddons);
        $this->assertSame(['MC', 3_000_000, 2], [
            $booking->bookingAddons[0]->addon_name, $booking->bookingAddons[0]->price, $booking->bookingAddons[0]->quantity,
        ]);

        // Harga master berubah setelah booking → booking tidak ikut berubah
        $this->package->update(['price' => 99_000_000]);
        $this->mc->update(['price' => 9_000_000]);
        $this->assertSame(56_000_000, $booking->fresh()->total_price);
    }

    public function test_booking_rejected_when_date_is_blocked(): void
    {
        $date = today()->addDays(45);
        BlockedDate::create(['date' => $date, 'reason' => 'Libur tim']);

        $this->actingAs($this->customer)
            ->post(route('bookings.store'), $this->payload(['event_date' => $date->toDateString()]))
            ->assertSessionHasErrors(['event_date' => 'Tanggal ini tidak tersedia.']);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_rejected_when_date_already_confirmed(): void
    {
        $date = today()->addDays(45);
        Booking::factory()->confirmed()->create(['event_date' => $date]);

        $this->actingAs($this->customer)
            ->post(route('bookings.store'), $this->payload(['event_date' => $date->toDateString()]))
            ->assertSessionHasErrors(['event_date' => 'Tanggal ini sudah dibooking pasangan lain.']);

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_booking_rejected_when_less_than_minimum_days(): void
    {
        $tooSoon = today()->addDays(config('restikey.min_booking_days') - 1);

        $this->actingAs($this->customer)
            ->post(route('bookings.store'), $this->payload(['event_date' => $tooSoon->toDateString()]))
            ->assertSessionHasErrors('event_date');

        // Tepat H+N boleh
        $this->actingAs($this->customer)
            ->post(route('bookings.store'), $this->payload(['event_date' => today()->addDays(config('restikey.min_booking_days'))->toDateString()]))
            ->assertSessionHasNoErrors();
    }

    public function test_pending_request_does_not_lock_date_but_warns(): void
    {
        $date = today()->addDays(60);
        Booking::factory()->create(['event_date' => $date]);

        $this->actingAs($this->customer)
            ->post(route('bookings.store'), $this->payload(['event_date' => $date->toDateString()]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('warning');

        $this->assertSame(2, Booking::pending()->onDate($date)->count());
    }

    public function test_guest_count_cannot_exceed_package_capacity_and_addon_quantity_is_limited(): void
    {
        $this->actingAs($this->customer)
            ->post(route('bookings.store'), $this->payload([
                'guest_count' => 501,
                'addons' => [$this->mc->id => ['selected' => '1', 'quantity' => config('restikey.max_addon_quantity') + 1]],
            ]))
            ->assertSessionHasErrors(['guest_count', 'addons.'.$this->mc->id]);
    }

    public function test_only_one_of_two_pending_bookings_on_same_date_can_be_confirmed(): void
    {
        $date = today()->addDays(70);
        [$first, $second] = Booking::factory(2)->create(['event_date' => $date]);
        $service = app(BookingService::class);

        $service->confirm($first);

        try {
            $service->confirm($second);
            $this->fail('Booking kedua seharusnya tidak bisa dikonfirmasi.');
        } catch (ValidationException $e) {
            // Konfirmasi pertama sudah membatalkan booking kedua (tanggal diambil pihak lain)
            $this->assertSame('Booking ini sudah diproses sebelumnya.', $e->errors()['booking'][0]);
        }

        $this->assertSame(Booking::STATUS_CONFIRMED, $first->fresh()->status);
        $this->assertNotNull($first->fresh()->confirmed_at);
        $this->assertSame(Booking::STATUS_CANCELLED, $second->fresh()->status);
        $this->assertSame(1, Booking::locking()->onDate($date)->count());
    }

    public function test_only_owner_can_view_booking(): void
    {
        $booking = Booking::factory()->for($this->customer)->create();

        $this->actingAs($this->customer)->get(route('bookings.show', $booking))
            ->assertOk()->assertSee($booking->booking_code);
        $this->actingAs(User::factory()->create())->get(route('bookings.show', $booking))
            ->assertForbidden();
    }

    public function test_guest_must_login_and_admin_cannot_create_booking(): void
    {
        $this->get(route('bookings.create'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('bookings.create'))->assertForbidden();
    }

    public function test_booking_form_prefills_package_and_date(): void
    {
        $date = today()->addDays(50)->toDateString();

        $this->actingAs($this->customer)
            ->get(route('bookings.create', ['package' => $this->package->slug, 'date' => $date]))
            ->assertOk()
            ->assertSee('value="'.$date.'"', false)
            ->assertSee('value="'.$this->package->id.'" selected', false);
    }

    public function test_cancel_requires_reason_and_only_pending_or_confirmed(): void
    {
        $booking = Booking::factory()->for($this->customer)->confirmed()->create();

        $this->actingAs($this->customer)->post(route('bookings.cancel', $booking), ['cancel_reason' => ''])
            ->assertSessionHasErrors('cancel_reason');

        $this->actingAs($this->customer)->post(route('bookings.cancel', $booking), ['cancel_reason' => 'Jadwal keluarga berubah'])
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertSame(Booking::STATUS_CANCELLED, $booking->fresh()->status);
        $this->assertSame('Jadwal keluarga berubah', $booking->fresh()->cancel_reason);

        $paid = Booking::factory()->for($this->customer)->paid()->create();
        $this->actingAs($this->customer)->post(route('bookings.cancel', $paid), ['cancel_reason' => 'Ingin batal saja'])
            ->assertForbidden();
    }

    public function test_customer_booking_list_shows_only_own_bookings_with_status_filter(): void
    {
        $mine = Booking::factory()->for($this->customer)->confirmed()->create();
        $minePending = Booking::factory()->for($this->customer)->create();
        $other = Booking::factory()->create();

        $this->actingAs($this->customer)->get(route('bookings.index', ['status' => 'confirmed']))
            ->assertOk()
            ->assertSee($mine->booking_code)
            ->assertDontSee($minePending->booking_code)
            ->assertDontSee($other->booking_code);
    }
}
