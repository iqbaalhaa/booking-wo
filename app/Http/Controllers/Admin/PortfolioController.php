<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PortfolioRequest;
use App\Models\Package;
use App\Models\Portfolio;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $portfolios = Portfolio::with('package')
            ->when($request->query('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->when($request->query('package'), fn ($query, $id) => $query->where('package_id', $id))
            ->latest('event_date')
            ->paginate(12)
            ->withQueryString();

        return view('admin.portfolios.index', [
            'portfolios' => $portfolios,
            'packages' => Package::orderBy('price')->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        return view('admin.portfolios.form', [
            'portfolio' => new Portfolio,
            'packages' => Package::orderBy('price')->get(['id', 'name']),
        ]);
    }

    public function store(PortfolioRequest $request)
    {
        $portfolio = Portfolio::create([
            ...$request->safe()->except('image'),
            'image' => $request->file('image')->store('portfolios', 'public'),
        ]);

        return redirect()->route('admin.portfolios.index')->with('success', "Portofolio {$portfolio->title} berhasil ditambahkan.");
    }

    public function edit(Portfolio $portfolio)
    {
        return view('admin.portfolios.form', [
            'portfolio' => $portfolio,
            'packages' => Package::orderBy('price')->get(['id', 'name']),
        ]);
    }

    public function update(PortfolioRequest $request, Portfolio $portfolio)
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('portfolios', 'public');
        }

        $portfolio->update($data); // file lama dihapus oleh DeletesReplacedFiles

        return redirect()->route('admin.portfolios.index')->with('success', "Portofolio {$portfolio->title} berhasil diperbarui.");
    }

    public function destroy(Portfolio $portfolio)
    {
        $portfolio->delete();

        return redirect()->route('admin.portfolios.index')->with('success', "Portofolio {$portfolio->title} berhasil dihapus.");
    }
}
