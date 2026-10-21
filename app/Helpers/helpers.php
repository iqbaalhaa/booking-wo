<?php

use Illuminate\Support\Carbon;

if (! function_exists('rupiah')) {
    /**
     * Format angka ke Rupiah, contoh: 15000000 → "Rp 15.000.000".
     */
    function rupiah(int|float|string|null $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('tanggal_indo')) {
    /**
     * Format tanggal Indonesia, contoh: "Sabtu, 21 November 2026".
     * Default tanpa nama hari: "21 November 2026".
     */
    function tanggal_indo(DateTimeInterface|string|null $date, bool $withDay = false, bool $withTime = false): string
    {
        if (blank($date)) {
            return '-';
        }

        $format = ($withDay ? 'dddd, ' : '').'D MMMM YYYY'.($withTime ? ' HH:mm' : '');

        return Carbon::parse($date)->locale('id')->isoFormat($format);
    }
}
