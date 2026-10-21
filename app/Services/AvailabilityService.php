<?php

namespace App\Services;

use App\Models\BlockedDate;
use App\Models\Booking;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AvailabilityService
{
    public const AVAILABLE = 'available';
    public const PENDING = 'pending';   // tersedia, tetapi sudah ada permintaan lain yang belum dikonfirmasi
    public const BOOKED = 'booked';
    public const BLOCKED = 'blocked';
    public const PAST = 'past';         // sudah lewat atau belum memenuhi minimal hari booking

    /** Status yang masih boleh dipilih customer. */
    public const SELECTABLE = [self::AVAILABLE, self::PENDING];

    private const CACHE_VERSION_KEY = 'availability:version';

    /** Tanggal acara paling cepat yang boleh dipesan hari ini (H+N dari config). */
    public function earliestDate(): Carbon
    {
        return today()->addDays(config('restikey.min_booking_days'));
    }

    public function isAvailable(Carbon $date): bool
    {
        return in_array($this->statusOf($date), self::SELECTABLE, true);
    }

    public function hasPendingRequest(Carbon $date, ?int $exceptBookingId = null): bool
    {
        return Booking::pending()
            ->onDate($date)
            ->when($exceptBookingId, fn ($query) => $query->whereKeyNot($exceptBookingId))
            ->exists();
    }

    /**
     * Versi ter-cache dari getMonthStatus() untuk tampilan (kalender & API).
     * Jangan dipakai untuk memutuskan boleh/tidaknya booking — itu selalu lewat query segar.
     * Kunci memuat tanggal hari ini (status "past" berubah tiap hari) dan versi yang diganti
     * setiap ada perubahan booking/tanggal diblokir.
     *
     * @return array<string, string>
     */
    public function cachedMonthStatus(int $year, int $month): array
    {
        $version = Cache::get(self::CACHE_VERSION_KEY, 'v0');
        $key = sprintf('availability:%s:%04d-%02d:%s', $version, $year, $month, today()->toDateString());

        return Cache::remember($key, now()->endOfDay(), fn () => $this->getMonthStatus($year, $month));
    }

    /** Dipanggil oleh event model Booking & BlockedDate. */
    public static function flushCache(): void
    {
        Cache::forever(self::CACHE_VERSION_KEY, (string) Str::uuid());
    }

    public function statusOf(Carbon $date): string
    {
        // Satu sumber logika status: ambil dari status bulanan
        return $this->getMonthStatus($date->year, $date->month)[$date->toDateString()];
    }

    /**
     * Status setiap tanggal dalam satu bulan, contoh: ['2026-12-01' => 'available', ...].
     *
     * @return array<string, string>
     */
    public function getMonthStatus(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->startOfDay();
        $range = [$start->toDateString(), $end->toDateString()];

        $booked = $this->dateSet(Booking::locking()->whereBetween('event_date', $range)->pluck('event_date'));
        $pending = $this->dateSet(Booking::pending()->whereBetween('event_date', $range)->pluck('event_date'));
        $blocked = $this->dateSet(BlockedDate::whereBetween('date', $range)->pluck('date'));
        $earliest = $this->earliestDate();

        $statuses = [];
        foreach (CarbonPeriod::create($start, $end) as $day) {
            $key = $day->toDateString();
            $statuses[$key] = match (true) {
                $day->lt($earliest) => self::PAST,
                isset($booked[$key]) => self::BOOKED,
                isset($blocked[$key]) => self::BLOCKED,
                isset($pending[$key]) => self::PENDING,
                default => self::AVAILABLE,
            };
        }

        return $statuses;
    }

    /** Pesan untuk customer per status. */
    public function messageFor(string $status): string
    {
        return match ($status) {
            self::AVAILABLE => 'Tanggal tersedia.',
            self::PENDING => 'Tanggal tersedia, tetapi sedang diminta pasangan lain. Booking yang dikonfirmasi lebih dulu yang mendapatkan tanggal ini.',
            self::BOOKED => 'Tanggal ini sudah dibooking pasangan lain.',
            self::BLOCKED => 'Tanggal ini tidak tersedia.',
            self::PAST => 'Tanggal acara paling cepat '.tanggal_indo($this->earliestDate(), true).' (minimal '.config('restikey.min_booking_days').' hari dari hari ini).',
        };
    }

    /** @return array<string, true> */
    private function dateSet($dates): array
    {
        return $dates->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => true])->all();
    }
}
