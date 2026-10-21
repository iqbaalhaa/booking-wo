<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $packages = Package::withCount(['features', 'bookings'])
            ->when($request->query('q'), fn ($query, $q) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('price')
            ->paginate(10)
            ->withQueryString();

        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.packages.form', ['package' => new Package(['is_active' => true])]);
    }

    public function store(PackageRequest $request)
    {
        $package = DB::transaction(function () use ($request) {
            $package = Package::create([
                ...$request->safe()->only(['name', 'description', 'price', 'max_guests', 'is_active']),
                'thumbnail' => $request->file('thumbnail')?->store('packages', 'public'),
            ]);

            $package->features()->createMany($this->featureRows($request));

            return $package;
        });

        return redirect()->route('admin.packages.index')->with('success', "Paket {$package->name} berhasil ditambahkan.");
    }

    public function edit(Package $package)
    {
        $package->load('features');

        return view('admin.packages.form', compact('package'));
    }

    public function update(PackageRequest $request, Package $package)
    {
        $data = $request->safe()->only(['name', 'description', 'price', 'max_guests', 'is_active']);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('packages', 'public');
        } elseif ($request->validated('remove_thumbnail')) {
            $data['thumbnail'] = null;
        }

        DB::transaction(function () use ($package, $data, $request) {
            $package->update($data); // file lama dihapus oleh DeletesReplacedFiles
            $package->features()->delete();
            $package->features()->createMany($this->featureRows($request));
        });

        return redirect()->route('admin.packages.index')->with('success', "Paket {$package->name} berhasil diperbarui.");
    }

    public function destroy(Package $package)
    {
        if ($count = $package->bookings()->count()) {
            return back()->with('error', "Paket {$package->name} sudah dipakai {$count} booking sehingga tidak bisa dihapus. Nonaktifkan saja agar tidak tampil untuk customer.");
        }

        $package->delete();

        return redirect()->route('admin.packages.index')->with('success', "Paket {$package->name} berhasil dihapus.");
    }

    public function toggle(Package $package)
    {
        $package->update(['is_active' => ! $package->is_active]);

        return back()->with('success', "Paket {$package->name} ".($package->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    private function featureRows(PackageRequest $request): array
    {
        return array_map(fn ($feature) => ['feature' => $feature], $request->validated('features'));
    }
}
