@extends('layouts.admin')

@section('title', 'Portofolio')

@section('content')
    <x-card body-class="p-0">
        <x-slot:actions>
            <form method="GET" class="d-flex flex-wrap gap-2" role="search">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto" placeholder="Cari judul" aria-label="Cari judul">
                <select name="package" class="form-select form-select-sm w-auto" aria-label="Filter paket">
                    <option value="">Semua paket</option>
                    @foreach ($packages as $package)
                        <option value="{{ $package->id }}" @selected(request('package') == $package->id)>{{ $package->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-primary btn-sm">Terapkan</button>
            </form>
            <a href="{{ route('admin.portfolios.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah portofolio
            </a>
        </x-slot:actions>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 72px"></th>
                        <th>Judul</th>
                        <th>Paket</th>
                        <th>Tanggal acara</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($portfolios as $portfolio)
                        <tr>
                            <td><img src="{{ $portfolio->image_url }}" alt="" class="admin-thumb" loading="lazy"></td>
                            <td class="fw-medium">{{ $portfolio->title }}</td>
                            <td>{{ $portfolio->package?->name ?? '-' }}</td>
                            <td class="text-nowrap">{{ tanggal_indo($portfolio->event_date) }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.portfolios.edit', $portfolio) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                <x-confirm-form :action="route('admin.portfolios.destroy', $portfolio)" method="DELETE"
                                                title="Hapus portofolio?" :message="'Portofolio '.$portfolio->title.' beserta gambarnya akan dihapus permanen.'"
                                                confirm-label="Hapus">
                                    Hapus
                                </x-confirm-form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-body-secondary py-4">Belum ada portofolio.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($portfolios->hasPages())
            <x-slot:footer>{{ $portfolios->links() }}</x-slot:footer>
        @endif
    </x-card>
@endsection
