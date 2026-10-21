<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * 10 booking dengan skenario status yang konsisten dengan aturan bisnis:
 * satu booking pengunci (confirmed/paid/completed) per tanggal, pembayaran sesuai status.
 */
class BookingSeeder extends Seeder
{
    private User $admin;

    public function run(): void
    {
        $this->admin = User::admins()->firstOrFail();
        $customers = User::customers()->get();
        $packages = Package::all()->keyBy('slug');
        $addons = Addon::all()->keyBy('name');

        $scenarios = [
            // [offset hari acara, paket, status, add-on [nama => qty], pembayaran]
            [45, 'silver', Booking::STATUS_PENDING, [], []],
            [45, 'gold', Booking::STATUS_PENDING, ['MC Profesional' => 1], []], // tanggal sama: demo peringatan
            [60, 'platinum', Booking::STATUS_PENDING, ['Photobooth' => 1], []],
            [75, 'gold', Booking::STATUS_CONFIRMED, [], []],
            [90, 'silver', Booking::STATUS_CONFIRMED, ['Katering Tambahan (100 pax)' => 2], ['dp:rejected', 'dp:pending']],
            [120, 'platinum', Booking::STATUS_CONFIRMED, ['Dokumentasi Cinematic' => 1, 'Live Music Akustik' => 1], ['dp:verified']],
            [50, 'gold', Booking::STATUS_PAID, ['Dekorasi Ekstra' => 1], ['dp:verified', 'pelunasan:verified']],
            [-20, 'platinum', Booking::STATUS_COMPLETED, ['MC Profesional' => 1], ['dp:verified', 'pelunasan:verified']],
            [-60, 'silver', Booking::STATUS_COMPLETED, [], ['dp:verified', 'pelunasan:verified']],
            [80, 'gold', Booking::STATUS_CANCELLED, [], []],
        ];

        foreach ($scenarios as $i => [$offset, $slug, $status, $addonQty, $payments]) {
            $booking = $this->createBooking(
                $customers[$i % $customers->count()],
                $packages[$slug],
                today()->addDays($offset),
                $status,
                collect($addonQty)->mapWithKeys(fn ($qty, $name) => [$addons[$name]->id => [$addons[$name], $qty]]),
            );

            foreach ($payments as $payment) {
                [$type, $paymentStatus] = explode(':', $payment);
                $this->createPayment($booking, $type, $paymentStatus);
            }

            if ($status === Booking::STATUS_COMPLETED) {
                Review::factory()->create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'rating' => 5,
                    // satu published untuk landing, satu menunggu moderasi
                    'is_published' => $offset === -20,
                ]);
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{0: Addon, 1: int}>  $addons
     */
    private function createBooking(User $user, Package $package, Carbon $eventDate, string $status, $addons): Booking
    {
        // Booking dibuat jauh sebelum acara agar memenuhi minimal H+N
        $createdAt = $eventDate->isPast()
            ? $eventDate->copy()->subDays(config('restikey.min_booking_days') + 60)
            : now()->subDays(25);

        // confirmed baru dikonfirmasi kemarin agar tidak langsung terkena auto-cancel
        $confirmedAt = match ($status) {
            Booking::STATUS_CONFIRMED => now()->subDay(),
            Booking::STATUS_PAID, Booking::STATUS_COMPLETED => $createdAt->copy()->addDay(),
            default => null,
        };

        $addonsTotal = $addons->sum(fn ($item) => $item[0]->price * $item[1]);

        $booking = Booking::factory()->create([
            'booking_code' => Booking::generateCode($createdAt),
            'user_id' => $user->id,
            'package_id' => $package->id,
            'event_date' => $eventDate,
            'guest_count' => min(rand(150, 800), $package->max_guests),
            'package_price' => $package->price,
            'addons_total' => $addonsTotal,
            'total_price' => $package->price + $addonsTotal,
            'status' => $status,
            'cancel_reason' => $status === Booking::STATUS_CANCELLED ? 'Jadwal keluarga berubah.' : null,
            'confirmed_at' => $confirmedAt,
            'completed_at' => $status === Booking::STATUS_COMPLETED ? $eventDate->copy()->addDay() : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        foreach ($addons as [$addon, $qty]) {
            $booking->bookingAddons()->create([
                'addon_id' => $addon->id,
                'addon_name' => $addon->name,
                'price' => $addon->price,
                'quantity' => $qty,
            ]);
        }

        return $booking;
    }

    private function createPayment(Booking $booking, string $type, string $status): Payment
    {
        $dp = (int) ceil($booking->total_price * config('restikey.dp_percentage') / 100);
        $paidAt = $type === Payment::TYPE_DP
            ? $booking->confirmed_at->copy()->addHours(2)
            : $booking->confirmed_at->copy()->addDays(10);

        return Payment::factory()->create([
            'booking_id' => $booking->id,
            'type' => $type,
            'amount' => $type === Payment::TYPE_DP ? $dp : $booking->total_price - $dp,
            'status' => $status,
            'rejection_reason' => $status === Payment::STATUS_REJECTED ? 'Nominal pada bukti transfer tidak sesuai.' : null,
            'verified_by' => $status === Payment::STATUS_PENDING ? null : $this->admin->id,
            'verified_at' => $status === Payment::STATUS_PENDING ? null : $paidAt->copy()->addHours(3),
            'created_at' => $paidAt,
            'updated_at' => $paidAt,
        ]);
    }
}
