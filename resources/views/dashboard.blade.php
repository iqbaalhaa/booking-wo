@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <h1 class="mb-1">Halo, {{ auth()->user()->name }}</h1>
        <p class="text-body-secondary mb-4">Pantau persiapan pernikahan Anda di sini.</p>

        <div class="row g-4">
            <div class="col-lg-8">
                @if ($nextEvent)
                    <div class="card countdown-card mb-4">
                        <div class="card-body p-4">
                            <div class="small">Menuju hari bahagia {{ $nextEvent->groom_name }} &amp; {{ $nextEvent->bride_name }}</div>
                            {{-- Angka awal dirender server; JS memperbarui setiap menit --}}
                            <div class="countdown" data-countdown="{{ $nextEvent->event_date->toIso8601String() }}" aria-live="off">
                                <span><strong data-unit="days">{{ (int) today()->diffInDays($nextEvent->event_date) }}</strong> hari</span>
                                <span><strong data-unit="hours">0</strong> jam</span>
                                <span><strong data-unit="minutes">0</strong> menit</span>
                            </div>
                            <div class="small">{{ tanggal_indo($nextEvent->event_date, true) }}, {{ $nextEvent->event_location }}</div>
                        </div>
                    </div>
                @endif

                <x-card title="Booking aktif" body-class="p-0">
                    <x-slot:actions>
                        <a href="{{ route('bookings.index') }}" class="btn btn-link btn-sm">Semua booking</a>
                    </x-slot:actions>

                    @forelse ($activeBookings as $booking)
                        <a href="{{ route('bookings.show', $booking) }}" class="booking-row">
                            <div>
                                <div class="fw-medium">{{ $booking->booking_code }}</div>
                                <div class="small text-body-secondary">Paket {{ $booking->package->name }}, {{ tanggal_indo($booking->event_date, true) }}</div>
                            </div>
                            <div class="text-end">
                                <x-badge-status :status="$booking->status" />
                                <div class="small mt-1">{{ rupiah($booking->total_price) }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-center">
                            <p class="text-body-secondary">Anda belum punya booking aktif.</p>
                            <a href="{{ route('packages.index') }}" class="btn btn-outline-primary">Lihat paket</a>
                        </div>
                    @endforelse
                </x-card>
            </div>

            <div class="col-lg-4">
                <x-card title="Tagihan">
                    @forelse ($unpaid as $booking)
                        @php($needsDp = ! $booking->hasVerifiedDp())
                        <div @class(['pb-3 mb-3 border-bottom' => ! $loop->last])>
                            <div class="small text-body-secondary">{{ $booking->booking_code }}</div>
                            <div class="fw-medium">
                                {{ $needsDp ? 'DP minimal '.rupiah($booking->minimumDp()) : 'Pelunasan '.rupiah($booking->remainingAmount()) }}
                            </div>
                            <div class="small">Batas {{ tanggal_indo($needsDp ? $booking->dpDeadline() : $booking->paymentDeadline(), true) }}</div>
                            @if ($booking->payments->contains('status', \App\Models\Payment::STATUS_PENDING))
                                <div class="small text-body-secondary mt-1">Bukti pembayaran sedang dicek.</div>
                            @else
                                <a href="{{ route('payments.create', $booking) }}" class="btn btn-primary btn-sm mt-2">Bayar sekarang</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-body-secondary mb-0">Tidak ada tagihan yang perlu dibayar.</p>
                    @endforelse
                </x-card>

                <x-card class="mt-4">
                    <p class="mb-2">Merencanakan acara lain?</p>
                    <a href="{{ route('availability.index') }}" class="btn btn-outline-primary w-100">Cek ketersediaan tanggal</a>
                </x-card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/countdown.js') }}"></script>
@endpush
