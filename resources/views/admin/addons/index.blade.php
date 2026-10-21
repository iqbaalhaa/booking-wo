@extends('layouts.admin')

@section('title', 'Add-on')

@section('content')
    <x-card body-class="p-0">
        <x-slot:actions>
            <form method="GET" class="d-flex gap-2" role="search">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari add-on" aria-label="Cari add-on">
                <button type="submit" class="btn btn-outline-primary btn-sm">Cari</button>
            </form>
            <a href="{{ route('admin.addons.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah add-on
            </a>
        </x-slot:actions>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th class="text-end">Harga</th>
                        <th class="text-center">Dipakai</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($addons as $addon)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $addon->name }}</div>
                                @if ($addon->description)
                                    <div class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($addon->description, 80) }}</div>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">{{ rupiah($addon->price) }}</td>
                            <td class="text-center">{{ $addon->booking_addons_count }}×</td>
                            <td>
                                <form method="POST" action="{{ route('admin.addons.toggle', $addon) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" @class(['badge rounded-pill border-0', 'text-bg-success' => $addon->is_active, 'text-bg-light border' => ! $addon->is_active])
                                            title="Klik untuk {{ $addon->is_active ? 'menonaktifkan' : 'mengaktifkan' }}">
                                        {{ $addon->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.addons.edit', $addon) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                <x-confirm-form :action="route('admin.addons.destroy', $addon)" method="DELETE"
                                                title="Hapus add-on?" confirm-label="Hapus"
                                                :message="$addon->booking_addons_count
                                                    ? 'Add-on '.$addon->name.' sudah dipakai '.$addon->booking_addons_count.' booking. Booking tersebut tetap menyimpan nama dan harganya. Lanjutkan hapus?'
                                                    : 'Add-on '.$addon->name.' akan dihapus permanen.'">
                                    Hapus
                                </x-confirm-form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-body-secondary py-4">
                            {{ request('q') ? 'Tidak ada add-on yang cocok dengan pencarian.' : 'Belum ada add-on.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($addons->hasPages())
            <x-slot:footer>{{ $addons->links() }}</x-slot:footer>
        @endif
    </x-card>
@endsection
