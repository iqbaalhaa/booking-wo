<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Portfolio;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $packages = Package::active()->orderBy('price')->get(['id', 'name', 'slug']);
        $selected = $packages->firstWhere('slug', $request->query('paket'));

        return view('portfolios.index', [
            'packages' => $packages,
            'selected' => $selected,
            'portfolios' => Portfolio::with('package')
                ->when($selected, fn ($query) => $query->where('package_id', $selected->id))
                ->latest('event_date')
                ->paginate(12)
                ->withQueryString(),
        ]);
    }
}
