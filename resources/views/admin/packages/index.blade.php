@extends('layouts.admin')

@section('title', 'Paket')

@section('content')
    <x-card body-class="p-0">
        <x-slot:actions>
            <form method="GET" class="d-flex gap-2" role="search">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama paket" aria-label="Cari nama paket">
                <button type="submit" class="btn btn-outline-primary btn-sm">Cari</button>
            </form>
            <a href="{{ route('admin.packages.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah paket
            </a>
        </x-slot:actions>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 72px"></th>
                        <th>Nama</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Maks. tamu</th>
                        <th class="text-center">Fitur</th>
                        <th class="text-center">Booking</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packages as $package)
                        <tr>
                            <td>
                                @if ($package->thumbnail_url)
                                    <img src="{{ $package->thumbnail_url }}" alt="" class="admin-thumb">
                                @else
                                    <div class="admin-thumb admin-thumb-empty"><i class="bi bi-image" aria-hidden="true"></i></div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">{{ $package->name }}</div>
                                <div class="small text-body-secondary">/paket/{{ $package->slug }}</div>
                            </td>
                            <td class="text-end text-nowrap">{{ rupiah($package->price) }}</td>
                            <td class="text-end">{{ number_format($package->max_guests, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $package->features_count }}</td>
                            <td class="text-center">{{ $package->bookings_count }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.packages.toggle', $package) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" @class(['badge rounded-pill border-0', 'text-bg-success' => $package->is_active, 'text-bg-light border' => ! $package->is_active])
                                            title="Klik untuk {{ $package->is_active ? 'menonaktifkan' : 'mengaktifkan' }}">
                                        {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-outline-primary btn-sm">Edit</a>

                                @if ($package->bookings_count)
                                    {{-- Sudah dipakai booking: tawarkan nonaktifkan, bukan hapus --}}
                                    @if ($package->is_active)
                                        <x-confirm-form :action="route('admin.packages.toggle', $package)" method="PATCH"
                                                        title="Paket tidak bisa dihapus"
                                                        :message="'Paket '.$package->name.' sudah dipakai '.$package->bookings_count.' booking sehingga tidak bisa dihapus. Nonaktifkan agar tidak tampil lagi untuk customer?'"
                                                        confirm-label="Nonaktifkan" confirm-class="btn-primary">
                                            Hapus
                                        </x-confirm-form>
                                    @endif
                                @else
                                    <x-confirm-form :action="route('admin.packages.destroy', $package)" method="DELETE"
                                                    title="Hapus paket?" :message="'Paket '.$package->name.' beserta fiturnya akan dihapus permanen.'"
                                                    confirm-label="Hapus">
                                        Hapus
                                    </x-confirm-form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-body-secondary py-4">
                            {{ request('q') ? 'Tidak ada paket yang cocok dengan pencarian.' : 'Belum ada paket. Tambahkan paket pertama Anda.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($packages->hasPages())
            <x-slot:footer>{{ $packages->links() }}</x-slot:footer>
        @endif
    </x-card>
@endsection
