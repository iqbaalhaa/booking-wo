@extends('layouts.app')

@section('title', 'Booking Saya')

@section('content')
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <h1 class="mb-1">Booking saya</h1>
                <p class="text-body-secondary mb-0">Riwayat dan status semua booking Anda.</p>
            </div>
            <a href="{{ route('bookings.create') }}" class="btn btn-primary">Booking baru</a>
        </div>

        <nav class="nav nav-pills gap-2 mb-4" aria-label="Filter status">
            <a @class(['nav-link', 'active' => ! $status]) href="{{ route('bookings.index') }}">Semua</a>
            @foreach (\App\Models\Booking::STATUSES as $key => $label)
                <a @class(['nav-link', 'active' => $status === $key]) href="{{ route('bookings.index', ['status' => $key]) }}"
                   @if ($status === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <x-card body-class="p-0">
            @forelse ($bookings as $booking)
                <a href="{{ route('bookings.show', $booking) }}" class="booking-row">
                    <div>
                        <div class="fw-medium">{{ $booking->booking_code }}</div>
                        <div class="small text-body-secondary">
                            Paket {{ $booking->package->name }}, {{ tanggal_indo($booking->event_date, true) }}
                        </div>
                        <div class="small text-body-secondary">{{ $booking->groom_name }} &amp; {{ $booking->bride_name }}</div>
                    </div>
                    <div class="text-end">
                        <x-badge-status :status="$booking->status" />
                        <div class="small mt-1">{{ rupiah($booking->total_price) }}</div>
                    </div>
                </a>
            @empty
                <div class="p-5 text-center">
                    <p class="text-body-secondary">{{ $status ? 'Tidak ada booking dengan status ini.' : 'Anda belum pernah booking.' }}</p>
                    <a href="{{ route('availability.index') }}" class="btn btn-outline-primary">Cek ketersediaan tanggal</a>
                </div>
            @endforelse

            @if ($bookings->hasPages())
                <x-slot:footer>{{ $bookings->links() }}</x-slot:footer>
            @endif
        </x-card>
    </div>
@endsection
