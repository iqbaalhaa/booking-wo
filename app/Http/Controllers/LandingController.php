<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Portfolio;
use App\Models\Review;

class LandingController extends Controller
{
    public function __invoke()
    {
        $packages = Package::active()
            ->with('features')
            ->withCount(['bookings' => fn ($query) => $query->whereIn('status', Booking::LOCKING_STATUSES)])
            ->orderBy('price')
            ->take(3)
            ->get();

        // "Paling banyak dipilih" hanya jika memang ada booking
        $popular = $packages->sortByDesc('bookings_count')->first();

        return view('landing', [
            'packages' => $packages,
            'popularId' => $popular?->bookings_count ? $popular->id : null,
            'addons' => Addon::active()->orderBy('price')->get(),
            'portfolios' => Portfolio::with('package')->latest('event_date')->take(6)->get(),
            'reviews' => Review::published()->with(['user', 'booking.package'])->latest()->take(6)->get(),
        ]);
    }
}
