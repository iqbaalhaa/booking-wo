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
        $packages = Package::activeCatalog()->take(3);

        // "Paling banyak dipilih" hanya jika memang ada booking yang terkonfirmasi
        $popularId = Booking::locking()
            ->whereIn('package_id', $packages->modelKeys())
            ->selectRaw('package_id, COUNT(*) as total')
            ->groupBy('package_id')
            ->orderByDesc('total')
            ->value('package_id');

        return view('landing', [
            'packages' => $packages,
            'popularId' => $popularId,
            'addons' => Addon::active()->orderBy('price')->get(),
            'portfolios' => Portfolio::with('package')->latest('event_date')->take(6)->get(),
            'reviews' => Review::published()->with(['user', 'booking.package'])->latest()->take(6)->get(),
        ]);
    }
}
