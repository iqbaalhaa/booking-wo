@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="stat-grid mb-4">
        @foreach ([
            ['Booking masuk bulan ini', $stats['bookingsThisMonth'], 'calendar-plus', route('admin.bookings.index')],
            ['Booking menunggu konfirmasi', $stats['pendingBookings'], 'hourglass-split', route('admin.bookings.index', ['status' => 'pending'])],
            ['Pembayaran menunggu verifikasi', $stats['pendingPayments'], 'receipt', route('admin.payments.index')],
            ['Pendapatan terverifikasi bulan ini', rupiah($stats['revenueThisMonth']), 'cash-stack', route('admin.reports.payments')],
            ['Acara 30 hari ke depan', $stats['upcomingEvents'], 'calendar-event', route('admin.bookings.index', ['dari' => today()->toDateString(), 'sampai' => today()->addDays(30)->toDateString()])],
        ] as [$label, $value, $icon, $url])
            <a href="{{ $url }}" class="card stat-card text-decoration-none">
                <div class="card-body d-flex flex-column gap-2">
                    <i class="bi bi-{{ $icon }} stat-icon" aria-hidden="true"></i>
                    <div class="min-w-0">
                        <div class="stat-value">{{ $value }}</div>
                        <div class="small text-body-secondary">{{ $label }}</div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        @foreach ([
            ['bookings', 'Booking per bulan', 'number', 'Jumlah booking'],
            ['revenue', 'Pendapatan terverifikasi per bulan', 'rupiah', 'Pendapatan'],
        ] as [$key, $title, $format, $seriesLabel])
            <div class="col-xl-6">
                <x-card :title="$title" class="h-100">
                    {{-- @json memecah argumen pada koma, jadi array disiapkan dulu --}}
                    @php($chart = ['labels' => $series['labels'], 'values' => $series[$key], 'format' => $format, 'label' => $seriesLabel])
                    <div class="chart-box">
                        <canvas data-chart='@json($chart)'
                                role="img" aria-label="{{ $title }}, 12 bulan terakhir. Data lengkap ada di tabel di bawah grafik."></canvas>
                    </div>
                    <details class="mt-2 small">
                        <summary>Lihat data dalam tabel</summary>
                        <table class="table table-sm mt-2 mb-0">
                            <thead><tr><th>Bulan</th><th class="text-end">{{ $seriesLabel }}</th></tr></thead>
                            <tbody>
                                @foreach ($series['labels'] as $i => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-end">{{ $format === 'rupiah' ? rupiah($series[$key][$i]) : $series[$key][$i] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                </x-card>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <x-card title="Acara terdekat (lunas)" body-class="p-0" class="h-100">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Tanggal</th><th>Mempelai</th><th>Paket</th></tr></thead>
                        <tbody>
                            @forelse ($nextEvents as $booking)
                                <tr>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.bookings.show', $booking) }}">{{ tanggal_indo($booking->event_date) }}</a>
                                        @php($daysLeft = (int) today()->diffInDays($booking->event_date))
                                        <div class="small text-body-secondary">{{ $daysLeft === 0 ? 'Hari ini' : $daysLeft.' hari lagi' }}</div>
                                    </td>
                                    <td>{{ $booking->groom_name }} &amp; {{ $booking->bride_name }}<div class="small text-body-secondary">{{ $booking->event_location }}</div></td>
                                    <td>{{ $booking->package->name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-body-secondary py-4">Belum ada acara lunas yang akan datang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <div class="col-xl-6">
            <x-card title="Booking terbaru" body-class="p-0" class="h-100">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Booking</th><th>Tanggal acara</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($latestBookings as $booking)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="text-nowrap">{{ $booking->booking_code }}</a>
                                        <div class="small text-body-secondary">{{ $booking->user->name }}, {{ $booking->package->name }}</div>
                                    </td>
                                    <td class="text-nowrap">{{ tanggal_indo($booking->event_date) }}</td>
                                    <td><x-badge-status :status="$booking->status" class="text-nowrap" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-body-secondary py-4">Belum ada booking.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endpush
