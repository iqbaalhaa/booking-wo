<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\ReportService;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports)
    {
        $monthRange = [now()->startOfMonth(), now()->endOfMonth()];

        return view('admin.dashboard', [
            'stats' => [
                'bookingsThisMonth' => Booking::whereBetween('created_at', $monthRange)->count(),
                'pendingBookings' => Booking::pending()->count(),
                'pendingPayments' => Payment::pending()->count(),
                'revenueThisMonth' => (int) Payment::verified()->whereBetween('verified_at', $monthRange)->sum('amount'),
                'upcomingEvents' => Booking::locking()->whereBetween('event_date', [today(), today()->addDays(30)])->count(),
            ],
            'series' => $reports->monthlySeries(12),
            'nextEvents' => Booking::paid()->with(['user', 'package'])->whereDate('event_date', '>=', today())->orderBy('event_date')->take(5)->get(),
            'latestBookings' => Booking::with(['user', 'package'])->latest()->take(5)->get(),
        ]);
    }
}
