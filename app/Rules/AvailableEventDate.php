<?php

namespace App\Rules;

use App\Services\AvailabilityService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

/**
 * Tanggal acara harus bisa dipesan: tidak diblokir, belum dibooking, dan memenuhi minimal hari booking.
 * Pakai setelah aturan format tanggal dengan 'bail'.
 */
class AvailableEventDate implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $availability = app(AvailabilityService::class);
        $status = $availability->statusOf(Carbon::parse($value)->startOfDay());

        if (! in_array($status, AvailabilityService::SELECTABLE, true)) {
            $fail($availability->messageFor($status));
        }
    }
}
