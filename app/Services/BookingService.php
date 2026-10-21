<?php

namespace App\Services;

use App\Mail\BookingCancelled;
use App\Mail\BookingConfirmed;
use App\Mail\BookingCreated;
use App\Models\Addon;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public const DATE_TAKEN_REASON = 'Tanggal telah dibooking pihak lain';

    public function __construct(private AvailabilityService $availability)
    {
    }

    /**
     * @param  array{package_id: int, event_date: string, event_location: string, groom_name: string,
     *               bride_name: string, guest_count: int, notes?: ?string, addons?: array<int, int>}  $data
     *         addons: [addon_id => quantity]
     */
    public function create(User $user, array $data): Booking
    {
        $date = Carbon::parse($data['event_date'])->startOfDay();

        $booking = DB::transaction(function () use ($user, $data, $date) {
            // Kunci semua booking di tanggal ini agar pengecekan & penyimpanan tidak balapan dengan konfirmasi lain
            Booking::onDate($date)->lockForUpdate()->get();

            $status = $this->availability->statusOf($date);
            if (! in_array($status, AvailabilityService::SELECTABLE, true)) {
                throw ValidationException::withMessages(['event_date' => $this->availability->messageFor($status)]);
            }

            $package = Package::active()->findOrFail($data['package_id']);

            // Snapshot harga saat booking; perubahan harga kemudian tidak memengaruhi booking ini
            $lines = Addon::active()
                ->whereIn('id', array_keys($data['addons'] ?? []))
                ->get()
                ->map(fn (Addon $addon) => [
                    'addon_id' => $addon->id,
                    'addon_name' => $addon->name,
                    'price' => $addon->price,
                    'quantity' => (int) $data['addons'][$addon->id],
                ]);

            $addonsTotal = $lines->sum(fn ($line) => $line['price'] * $line['quantity']);

            $booking = $user->bookings()->create([
                'booking_code' => Booking::generateCode(),
                'package_id' => $package->id,
                'event_date' => $date,
                'event_location' => $data['event_location'],
                'groom_name' => $data['groom_name'],
                'bride_name' => $data['bride_name'],
                'guest_count' => $data['guest_count'],
                'notes' => $data['notes'] ?? null,
                'package_price' => $package->price,
                'addons_total' => $addonsTotal,
                'total_price' => $package->price + $addonsTotal,
                'status' => Booking::STATUS_PENDING,
            ]);

            $booking->bookingAddons()->createMany($lines->all());

            return $booking;
        });

        Mail::to($user)->send(new BookingCreated($booking));

        if (($admins = User::admins()->get())->isNotEmpty()) {
            Mail::to($admins)->send(new BookingCreated($booking, forAdmin: true));
        }

        return $booking;
    }

    /**
     * Konfirmasi booking pending. Hanya satu booking per tanggal yang bisa lolos walau dua admin
     * mengonfirmasi bersamaan; booking pending lain di tanggal itu dibatalkan otomatis.
     */
    public function confirm(Booking $booking): Booking
    {
        [$booking, $displaced] = DB::transaction(function () use ($booking) {
            // user di-eager-load untuk email ke customer terkonfirmasi & yang tergeser
            $sameDate = Booking::onDate($booking->event_date)->with('user')->lockForUpdate()->get();
            $booking = $sameDate->firstWhere('id', $booking->id);

            if ($booking->status !== Booking::STATUS_PENDING) {
                throw ValidationException::withMessages(['booking' => 'Booking ini sudah diproses sebelumnya.']);
            }

            if ($sameDate->contains(fn (Booking $other) => in_array($other->status, Booking::LOCKING_STATUSES, true))) {
                throw ValidationException::withMessages(['booking' => 'Tanggal ini sudah dikonfirmasi untuk booking lain.']);
            }

            if (BlockedDate::whereDate('date', $booking->event_date)->exists()) {
                throw ValidationException::withMessages(['booking' => 'Tanggal ini sedang diblokir.']);
            }

            $booking->update(['status' => Booking::STATUS_CONFIRMED, 'confirmed_at' => now()]);

            $displaced = $sameDate->filter(fn (Booking $other) => $other->isNot($booking) && $other->status === Booking::STATUS_PENDING);
            $displaced->each->update(['status' => Booking::STATUS_CANCELLED, 'cancel_reason' => self::DATE_TAKEN_REASON]);

            return [$booking, $displaced];
        });

        Mail::to($booking->user)->send(new BookingConfirmed($booking));
        $displaced->each(fn (Booking $other) => Mail::to($other->user)->send(new BookingCancelled($other)));

        return $booking;
    }

    public function cancel(Booking $booking, string $reason): Booking
    {
        $booking = DB::transaction(function () use ($booking, $reason) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);

            if (! $booking->isCancellable()) {
                throw ValidationException::withMessages(['cancel_reason' => 'Booking dengan status '.$booking->statusLabel().' tidak bisa dibatalkan.']);
            }

            $booking->update(['status' => Booking::STATUS_CANCELLED, 'cancel_reason' => $reason]);

            return $booking;
        });

        Mail::to($booking->user)->send(new BookingCancelled($booking));

        return $booking;
    }

    /** Tandai selesai: hanya booking lunas yang tanggal acaranya sudah lewat. */
    public function complete(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);

            if ($booking->status !== Booking::STATUS_PAID || ! $booking->event_date->lt(today())) {
                throw ValidationException::withMessages(['booking' => 'Hanya booking lunas yang acaranya sudah lewat yang bisa ditandai selesai.']);
            }

            $booking->update(['status' => Booking::STATUS_COMPLETED, 'completed_at' => now()]);

            return $booking;
        });
    }
}
