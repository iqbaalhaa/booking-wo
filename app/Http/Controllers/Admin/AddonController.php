<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddonRequest;
use App\Models\Addon;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    public function index(Request $request)
    {
        $addons = Addon::withCount('bookingAddons')
            ->when($request->query('q'), fn ($query, $q) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.addons.index', compact('addons'));
    }

    public function create()
    {
        return view('admin.addons.form', ['addon' => new Addon(['is_active' => true])]);
    }

    public function store(AddonRequest $request)
    {
        $addon = Addon::create($request->validated());

        return redirect()->route('admin.addons.index')->with('success', "Add-on {$addon->name} berhasil ditambahkan.");
    }

    public function edit(Addon $addon)
    {
        return view('admin.addons.form', compact('addon'));
    }

    public function update(AddonRequest $request, Addon $addon)
    {
        $addon->update($request->validated());

        return redirect()->route('admin.addons.index')->with('success', "Add-on {$addon->name} berhasil diperbarui.");
    }

    public function destroy(Addon $addon)
    {
        // Booking lama tetap menyimpan nama & harga add-on (snapshot), addon_id di-null-kan oleh foreign key
        $addon->delete();

        return redirect()->route('admin.addons.index')->with('success', "Add-on {$addon->name} berhasil dihapus.");
    }

    public function toggle(Addon $addon)
    {
        $addon->update(['is_active' => ! $addon->is_active]);

        return back()->with('success', "Add-on {$addon->name} ".($addon->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }
}
