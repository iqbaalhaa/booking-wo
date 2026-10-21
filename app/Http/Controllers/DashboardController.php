<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $activeBookings = $request->user()->bookings()
            ->with(['package', 'payments'])
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED, Booking::STATUS_PAID])
            ->orderBy('event_date')
            ->get();

        return view('dashboard', [
            'activeBookings' => $activeBookings,
            // Hitung mundur ke acara terdekat yang tanggalnya sudah pasti (confirmed/paid)
            'nextEvent' => $activeBookings->first(fn (Booking $booking) => $booking->status !== Booking::STATUS_PENDING && $booking->event_date->gte(today())),
            'unpaid' => $activeBookings->filter(fn (Booking $booking) => $booking->status === Booking::STATUS_CONFIRMED && $booking->remainingAmount() > 0),
        ]);
    }
}
