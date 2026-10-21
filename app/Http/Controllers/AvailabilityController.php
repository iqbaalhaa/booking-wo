<?php

namespace App\Http\Controllers;

use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AvailabilityController extends Controller
{
    public function __construct(private AvailabilityService $availability)
    {
    }

    /** Halaman kalender /cek-tanggal?bulan=YYYY-MM */
    public function index(Request $request)
    {
        $first = today()->startOfMonth();
        $last = $first->copy()->addMonths(config('restikey.calendar_months_ahead'));

        $month = ($this->parseMonth($request->query('bulan')) ?? $first)->max($first)->min($last);

        return view('availability.index', [
            'month' => $month,
            'statuses' => $this->availability->cachedMonthStatus($month->year, $month->month),
            'prevMonth' => $month->gt($first) ? $month->copy()->subMonth() : null,
            'nextMonth' => $month->lt($last) ? $month->copy()->addMonth() : null,
            'earliest' => $this->availability->earliestDate(),
        ]);
    }

    /** JSON untuk form booking: GET /api/availability?month=YYYY-MM */
    public function month(Request $request)
    {
        $month = $this->parseMonth($request->query('month'));

        abort_unless($month, 422, 'Parameter month harus berformat YYYY-MM.');

        return response()->json([
            'month' => $month->format('Y-m'),
            'earliest' => $this->availability->earliestDate()->toDateString(),
            'dates' => $this->availability->cachedMonthStatus($month->year, $month->month),
        ]);
    }

    private function parseMonth(?string $value): ?Carbon
    {
        if (! $value || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfDay();
    }
}
