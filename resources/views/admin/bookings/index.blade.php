@extends('layouts.admin')

@section('title', 'Booking')

@section('content')
    <nav class="nav nav-pills gap-2 mb-3" aria-label="Filter status booking">
        <a @class(['nav-link', 'active' => ! $status]) href="{{ route('admin.bookings.index', request()->except(['status', 'page'])) }}">
            Semua <span class="badge text-bg-light border ms-1">{{ $counts->sum() }}</span>
        </a>
        @foreach (\App\Models\Booking::STATUSES as $key => $label)
            <a @class(['nav-link', 'active' => $status === $key]) href="{{ route('admin.bookings.index', [...request()->except(['status', 'page']), 'status' => $key]) }}"
               @if ($status === $key) aria-current="page" @endif>
                {{ $label }} <span class="badge text-bg-light border ms-1">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </nav>

    <x-card body-class="p-0">
        <x-slot:actions>
            <form method="GET" class="row g-2 align-items-end w-100" role="search">
                @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                <div class="col-md-4">
                    <label for="q" class="form-label small mb-1">Cari</label>
                    <input id="q" type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Kode, nama mempelai, atau customer">
                </div>
                <div class="col-6 col-md-3">
                    <label for="dari" class="form-label small mb-1">Tanggal acara dari</label>
                    <input id="dari" type="date" name="dari" value="{{ request('dari') }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-3">
                    <label for="sampai" class="form-label small mb-1">sampai</label>
                    <input id="sampai" type="date" name="sampai" value="{{ request('sampai') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Terapkan</button>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-light btn-sm" title="Reset filter">Reset</a>
                </div>
            </form>
        </x-slot:actions>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Customer</th>
                        <th class="d-none d-md-table-cell">Mempelai</th>
                        <th class="d-none d-md-table-cell">Paket</th>
                        <th>Tanggal acara</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="text-nowrap"><a href="{{ route('admin.bookings.show', $booking) }}" class="fw-medium">{{ $booking->booking_code }}</a></td>
                            <td>{{ $booking->user->name }}</td>
                            <td class="small d-none d-md-table-cell">{{ $booking->groom_name }} &amp; {{ $booking->bride_name }}</td>
                            <td class="d-none d-md-table-cell">{{ $booking->package->name }}</td>
                            <td class="text-nowrap">{{ tanggal_indo($booking->event_date) }}</td>
                            <td class="text-end text-nowrap">{{ rupiah($booking->total_price) }}</td>
                            <td><x-badge-status :status="$booking->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-body-secondary py-4">Tidak ada booking yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bookings->hasPages())
            <x-slot:footer>{{ $bookings->links() }}</x-slot:footer>
        @endif
    </x-card>
@endsection
