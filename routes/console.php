<?php

use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
 * Batalkan booking confirmed yang DP-nya belum terverifikasi lewat N hari setelah konfirmasi.
 * Booking yang bukti DP-nya sedang menunggu pengecekan admin tidak ikut dibatalkan
 * (customer sudah membayar tepat waktu, keterlambatan ada di sisi verifikasi).
 */
Artisan::command('bookings:auto-cancel', function (BookingService $service) {
    $days = config('restikey.auto_cancel_days');

    $bookings = Booking::confirmed()
        ->where('confirmed_at', '<=', now()->subDays($days))
        ->whereDoesntHave('payments', fn ($query) => $query
            ->where(fn ($query) => $query->where('type', Payment::TYPE_DP)->where('status', Payment::STATUS_VERIFIED))
            ->orWhere('status', Payment::STATUS_PENDING))
        ->get();

    foreach ($bookings as $booking) {
        $service->cancel($booking, "Dibatalkan otomatis: DP belum diterima dalam {$days} hari setelah konfirmasi.");
        $this->line("Dibatalkan: {$booking->booking_code}");
    }

    $this->info("{$bookings->count()} booking dibatalkan otomatis.");
})->purpose('Batalkan booking confirmed yang belum membayar DP melewati batas hari di config');

Schedule::command('bookings:auto-cancel')->dailyAt('01:00')->withoutOverlapping();
