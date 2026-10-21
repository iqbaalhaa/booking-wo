@extends('layouts.admin')

@section('title', 'Tanggal Diblokir')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <x-card title="Blokir tanggal">
                <p class="small text-body-secondary">
                    Tanggal yang diblokir tidak bisa dipilih customer. Isi tanggal selesai untuk memblokir rentang tanggal sekaligus.
                </p>

                <form method="POST" action="{{ route('admin.blocked-dates.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Tanggal mulai</label>
                        <input id="start_date" name="start_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('start_date') }}"
                               class="form-control @error('start_date') is-invalid @enderror" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">Tanggal selesai (opsional)</label>
                        <input id="end_date" name="end_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('end_date') }}"
                               class="form-control @error('end_date') is-invalid @enderror">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan (opsional)</label>
                        <input id="reason" name="reason" value="{{ old('reason') }}" maxlength="255" placeholder="Libur tim"
                               class="form-control @error('reason') is-invalid @enderror">
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Blokir</button>
                </form>
            </x-card>
        </div>

        <div class="col-lg-8">
            <x-card :title="$showPast ? 'Semua tanggal diblokir' : 'Tanggal diblokir mendatang'" body-class="p-0">
                <x-slot:actions>
                    <a href="{{ route('admin.blocked-dates.index', $showPast ? [] : ['lewat' => 1]) }}" class="btn btn-link btn-sm">
                        {{ $showPast ? 'Hanya tampilkan yang mendatang' : 'Tampilkan juga yang sudah lewat' }}
                    </a>
                </x-slot:actions>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Alasan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($blockedDates as $blocked)
                                <tr @class(['text-body-secondary' => $blocked->date->isPast() && ! $blocked->date->isToday()])>
                                    <td class="text-nowrap">{{ tanggal_indo($blocked->date, true) }}</td>
                                    <td>{{ $blocked->reason ?? '-' }}</td>
                                    <td class="text-end">
                                        <x-confirm-form :action="route('admin.blocked-dates.destroy', $blocked)" method="DELETE"
                                                        title="Buka tanggal ini?" :message="tanggal_indo($blocked->date, true).' akan bisa dipilih lagi oleh customer.'"
                                                        confirm-label="Buka tanggal" confirm-class="btn-primary">
                                            Hapus
                                        </x-confirm-form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-body-secondary py-4">Tidak ada tanggal yang diblokir.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($blockedDates->hasPages())
                    <x-slot:footer>{{ $blockedDates->links() }}</x-slot:footer>
                @endif
            </x-card>
        </div>
    </div>
@endsection
