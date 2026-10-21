<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Package;

class PackageController extends Controller
{
    public function index()
    {
        return view('packages.index', [
            'packages' => Package::activeCatalog(),
        ]);
    }

    public function show(Package $package)
    {
        abort_unless($package->is_active, 404);

        $package->load('features');

        return view('packages.show', [
            'package' => $package,
            'addons' => Addon::active()->orderBy('price')->get(),
            'portfolios' => $package->portfolios()->latest('event_date')->take(3)->get(),
            'reviews' => $package->reviews()->published()->with('user')->latest('reviews.created_at')->take(6)->get(),
        ]);
    }
}
