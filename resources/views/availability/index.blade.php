@extends('layouts.app')

@section('title', 'Cek Tanggal')
@section('description', 'Cek ketersediaan tanggal pernikahan Anda di Restikey Wedding Organizer.')

@php
    $labels = [
        'available' => 'Tersedia',
        'pending' => 'Sedang diminta',
        'booked' => 'Sudah dibooking',
        'blocked' => 'Tidak tersedia',
        'past' => 'Belum bisa dipesan',
    ];
    $selectable = \App\Services\AvailabilityService::SELECTABLE;
    $leadingBlanks = $month->dayOfWeekIso - 1; // minggu dimulai hari Senin
@endphp

@section('content')
    <div class="container">
        <div class="row g-4 align-items-end mb-4">
            <div class="col-lg-7">
                <h1 class="section-title">Cek ketersediaan tanggal</h1>
                <p class="section-lead mb-0">
                    Pilih tanggal yang tersedia untuk langsung mengisi form booking. Tanggal paling cepat yang bisa
                    dipesan hari ini adalah <strong>{{ tanggal_indo($earliest, true) }}</strong>.
                </p>
            </div>
            <div class="col-lg-5">
                <ul class="calendar-legend" aria-label="Keterangan warna">
                    @foreach ($labels as $status => $label)
                        <li><span class="legend-swatch day-{{ $status }}" aria-hidden="true"></span>{{ $label }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="calendar">
            <div class="calendar-header">
                @if ($prevMonth)
                    <a href="{{ route('availability.index', ['bulan' => $prevMonth->format('Y-m')]) }}" class="btn btn-outline-primary btn-sm"
                       aria-label="Bulan sebelumnya, {{ $prevMonth->locale('id')->isoFormat('MMMM YYYY') }}">
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </a>
                @else
                    <span class="btn btn-outline-primary btn-sm disabled" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                @endif

                <h2 class="h3 mb-0" aria-live="polite">{{ $month->locale('id')->isoFormat('MMMM YYYY') }}</h2>

                @if ($nextMonth)
                    <a href="{{ route('availability.index', ['bulan' => $nextMonth->format('Y-m')]) }}" class="btn btn-outline-primary btn-sm"
                       aria-label="Bulan berikutnya, {{ $nextMonth->locale('id')->isoFormat('MMMM YYYY') }}">
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </a>
                @else
                    <span class="btn btn-outline-primary btn-sm disabled" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                @endif
            </div>

            <div class="calendar-grid" aria-label="Kalender {{ $month->locale('id')->isoFormat('MMMM YYYY') }}">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                    <div class="calendar-dow" aria-hidden="true">{{ $dayName }}</div>
                @endforeach

                @for ($i = 0; $i < $leadingBlanks; $i++)
                    <div class="calendar-day calendar-blank" aria-hidden="true"></div>
                @endfor

                @foreach ($statuses as $date => $status)
                    @php($day = \Illuminate\Support\Carbon::parse($date))
                    @if (in_array($status, $selectable, true))
                        <a href="{{ route('bookings.create', ['date' => $date]) }}" class="calendar-day day-{{ $status }}"
                           aria-label="{{ tanggal_indo($day, true) }}, {{ strtolower($labels[$status]) }}. Pilih untuk booking.">
                            <span class="day-number">{{ $day->day }}</span>
                            <span class="day-label">{{ $labels[$status] }}</span>
                        </a>
                    @else
                        <div class="calendar-day day-{{ $status }}" aria-label="{{ tanggal_indo($day, true) }}, {{ strtolower($labels[$status]) }}">
                            <span class="day-number">{{ $day->day }}</span>
                            @if ($status !== 'past')
                                <span class="day-label">{{ $labels[$status] }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <p class="small text-body-secondary mt-3">
            "Sedang diminta" berarti sudah ada pasangan lain yang mengajukan booking tetapi belum dikonfirmasi.
            Tanggal tersebut masih bisa Anda ajukan; booking yang dikonfirmasi lebih dulu yang mendapatkan tanggalnya.
        </p>
    </div>
@endsection
