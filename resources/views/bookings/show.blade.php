@extends('layouts.app')

@section('title', 'Booking '.$booking->booking_code)

@use('App\Models\Booking')
@use('App\Models\Payment')

@php
    $verifiedPayments = $booking->payments->where('status', Payment::STATUS_VERIFIED)->sortBy('verified_at');
    $firstDp = $verifiedPayments->firstWhere('type', Payment::TYPE_DP);
    $hasPendingPayment = $booking->payments->contains('status', Payment::STATUS_PENDING);
    $cancelled = $booking->status === Booking::STATUS_CANCELLED;

    // [judul, waktu (null = belum), keterangan]
    $timeline = [
        ['Booking dikirim', $booking->created_at, null],
        ['Dikonfirmasi tim Restikey', $booking->confirmed_at, null],
        ['DP diverifikasi', $firstDp?->verified_at, $firstDp ? rupiah($firstDp->amount) : null],
        ['Pembayaran lunas', in_array($booking->status, [Booking::STATUS_PAID, Booking::STATUS_COMPLETED], true) ? $verifiedPayments->last()?->verified_at : null, null],
        ['Hari acara', $booking->event_date->isPast() && ! $cancelled ? $booking->event_date : null, tanggal_indo($booking->event_date, true)],
        ['Acara selesai', $booking->completed_at, null],
    ];

    if ($cancelled) {
        // Tampilkan langkah yang sudah terjadi, lalu pembatalan
        $timeline = array_filter($timeline, fn ($step) => $step[1] !== null);
        $timeline[] = ['Dibatalkan', $booking->updated_at, $booking->cancel_reason];
    }
@endphp

@section('content')
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Booking saya</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $booking->booking_code }}</li>
            </ol>
        </nav>

        @error('cancel_reason')
            <x-alert type="error">{{ $message }}</x-alert>
        @enderror

        <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
            <h1 class="mb-0">{{ $booking->groom_name }} &amp; {{ $booking->bride_name }}</h1>
            <x-badge-status :status="$booking->status" class="fs-6" />
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <x-card title="Detail acara">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Kode booking</dt>
                        <dd class="col-sm-8">{{ $booking->booking_code }}</dd>
                        <dt class="col-sm-4">Tanggal acara</dt>
                        <dd class="col-sm-8">{{ tanggal_indo($booking->event_date, true) }}</dd>
                        <dt class="col-sm-4">Lokasi</dt>
                        <dd class="col-sm-8">{{ $booking->event_location }}</dd>
                        <dt class="col-sm-4">Paket</dt>
                        <dd class="col-sm-8">{{ $booking->package->name }}</dd>
                        <dt class="col-sm-4">Perkiraan tamu</dt>
                        <dd class="col-sm-8">{{ number_format($booking->guest_count, 0, ',', '.') }} orang</dd>
                        @if ($booking->notes)
                            <dt class="col-sm-4">Catatan</dt>
                            <dd class="col-sm-8">{{ $booking->notes }}</dd>
                        @endif
                        <dt class="col-sm-4">Dibuat</dt>
                        <dd class="col-sm-8 mb-0">{{ tanggal_indo($booking->created_at, true, true) }}</dd>
                    </dl>
                </x-card>

                <x-card title="Rincian harga" class="mt-4" body-class="p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td>Paket {{ $booking->package->name }}</td>
                                    <td class="text-end text-nowrap">{{ rupiah($booking->package_price) }}</td>
                                </tr>
                                @foreach ($booking->bookingAddons as $line)
                                    <tr>
                                        <td>{{ $line->addon_name }} <span class="text-body-secondary">({{ $line->quantity }} × {{ rupiah($line->price) }})</span></td>
                                        <td class="text-end text-nowrap">{{ rupiah($line->subtotal()) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="fw-semibold">
                                    <td>Total</td>
                                    <td class="text-end text-nowrap">{{ rupiah($booking->total_price) }}</td>
                                </tr>
                                <tr class="small text-body-secondary">
                                    <td>Sudah dibayar (terverifikasi)</td>
                                    <td class="text-end text-nowrap">{{ rupiah($booking->verifiedTotal()) }}</td>
                                </tr>
                                @unless ($cancelled)
                                    <tr class="small">
                                        <td>Sisa tagihan</td>
                                        <td class="text-end text-nowrap">{{ rupiah($booking->remainingAmount()) }}</td>
                                    </tr>
                                @endunless
                            </tbody>
                        </table>
                    </div>
                </x-card>

                <x-card title="Riwayat pembayaran" class="mt-4" body-class="p-0">
                    @if ($booking->payments->isEmpty())
                        <p class="text-body-secondary p-3 mb-0">Belum ada pembayaran.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr><th>Tanggal</th><th>Jenis</th><th class="text-end">Nominal</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($booking->payments as $payment)
                                        <tr>
                                            <td class="text-nowrap">{{ tanggal_indo($payment->created_at) }}</td>
                                            <td>
                                                {{ $payment->typeLabel() }}
                                                <a href="{{ $payment->proof_url }}" target="_blank" rel="noopener" class="d-block small">Lihat bukti</a>
                                            </td>
                                            <td class="text-end text-nowrap">{{ rupiah($payment->amount) }}</td>
                                            <td>
                                                <x-badge-status :status="$payment->status" type="payment" />
                                                @if ($payment->rejection_reason)
                                                    <div class="small text-danger mt-1">{{ $payment->rejection_reason }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-card>

                @can('review', $booking)
                    <x-card title="Beri ulasan" class="mt-4" id="ulasan">
                        <form method="POST" action="{{ route('reviews.store', $booking) }}" novalidate>
                            @csrf

                            <fieldset class="mb-3">
                                <legend class="form-label fs-6">Bagaimana pengalaman Anda bersama kami?</legend>
                                {{-- Urutan DOM 5→1 dibalik oleh CSS agar bintang terisi dari kiri saat dipilih/hover --}}
                                <div class="star-rating @error('rating') is-invalid @enderror">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <input type="radio" name="rating" value="{{ $i }}" id="rating-{{ $i }}" @checked(old('rating') == $i) required>
                                        <label for="rating-{{ $i }}" title="{{ $i }} bintang">
                                            <i class="bi bi-star-fill" aria-hidden="true"></i><span class="visually-hidden">{{ $i }} bintang</span>
                                        </label>
                                    @endfor
                                </div>
                                @error('rating')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </fieldset>

                            <div class="mb-3">
                                <label for="comment" class="form-label">Ceritakan pengalaman Anda</label>
                                <textarea id="comment" name="comment" rows="4" maxlength="1000" required
                                          class="form-control @error('comment') is-invalid @enderror">{{ old('comment') }}</textarea>
                                @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary">Kirim ulasan</button>
                            <span class="small text-body-secondary ms-2">Ulasan tampil di website setelah ditinjau tim kami.</span>
                        </form>
                    </x-card>
                @endcan

                @if ($booking->review)
                    <x-card title="Ulasan Anda" class="mt-4">
                        <div class="rating-stars mb-2" aria-label="Rating {{ $booking->review->rating }} dari 5">
                            @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star{{ $i <= $booking->review->rating ? '-fill' : '' }}" aria-hidden="true"></i>@endfor
                        </div>
                        <p class="mb-1">{{ $booking->review->comment }}</p>
                        <div class="small text-body-secondary">
                            {{ $booking->review->is_published ? 'Tampil di website.' : 'Menunggu ditinjau tim kami.' }}
                        </div>
                    </x-card>
                @endif
            </div>

            <div class="col-lg-4">
                <x-card title="Langkah berikutnya">
                    @switch($booking->status)
                        @case(Booking::STATUS_PENDING)
                            <p>Tim kami sedang memeriksa jadwal. Setelah booking dikonfirmasi, Anda bisa membayar DP minimal
                                <strong>{{ rupiah($booking->minimumDp()) }}</strong>.</p>
                            @break

                        @case(Booking::STATUS_CONFIRMED)
                            @if ($hasPendingPayment)
                                <p>Pembayaran Anda sedang diverifikasi oleh tim kami.</p>
                            @elseif (! $booking->hasVerifiedDp())
                                <p>Bayar DP minimal <strong>{{ rupiah($booking->minimumDp()) }}</strong> paling lambat
                                    <strong>{{ tanggal_indo($booking->dpDeadline(), true) }}</strong>. Jika lewat, booking dibatalkan otomatis.</p>
                            @else
                                <p>Lunasi sisa tagihan <strong>{{ rupiah($booking->remainingAmount()) }}</strong> paling lambat
                                    <strong>{{ tanggal_indo($booking->paymentDeadline(), true) }}</strong>.</p>
                            @endif

                            @can('pay', $booking)
                                @if (Route::has('payments.create') && ! $hasPendingPayment)
                                    <a href="{{ route('payments.create', $booking) }}" class="btn btn-primary w-100 mb-2">
                                        {{ $booking->hasVerifiedDp() ? 'Bayar pelunasan' : 'Bayar DP' }}
                                    </a>
                                @endif
                            @endcan

                            <div class="small text-body-secondary">
                                Transfer ke {{ config('restikey.bank.name') }} {{ config('restikey.bank.account_number') }}
                                a.n. {{ config('restikey.bank.account_name') }}.
                            </div>
                            @break

                        @case(Booking::STATUS_PAID)
                            <p class="mb-0">Pembayaran sudah lunas. Sampai jumpa di hari bahagia Anda!</p>
                            @break

                        @case(Booking::STATUS_COMPLETED)
                            @if ($booking->review)
                                <p class="mb-0">Terima kasih atas ulasan Anda.</p>
                            @else
                                <p>Acara sudah selesai. Ceritakan pengalaman Anda bersama kami.</p>
                                @can('review', $booking)
                                    <a href="#ulasan" class="btn btn-primary w-100">Beri ulasan</a>
                                @endcan
                            @endif
                            @break

                        @case(Booking::STATUS_CANCELLED)
                            <p class="mb-0">Booking ini telah dibatalkan.</p>
                            @break
                    @endswitch

                    @can('cancel', $booking)
                        <hr>
                        <x-confirm-form :action="route('bookings.cancel', $booking)" title="Batalkan booking?"
                                        message="Booking yang dibatalkan tidak bisa diaktifkan kembali dan tanggalnya akan terbuka untuk pasangan lain."
                                        reason="cancel_reason" reason-label="Alasan pembatalan" confirm-label="Ya, batalkan"
                                        button-class="btn btn-outline-danger w-100">
                            Batalkan booking
                        </x-confirm-form>
                    @endcan
                </x-card>

                <x-card title="Status" class="mt-4" body-class="p-0">
                    <ol class="list-group list-group-flush timeline">
                        @foreach ($timeline as [$title, $time, $note])
                            @php($isCancelStep = $cancelled && $loop->last)
                            <li @class(['list-group-item timeline-step', 'is-done' => $time && ! $isCancelStep, 'is-cancelled' => $isCancelStep])>
                                <i @class(['bi', 'bi-check-circle-fill' => $time && ! $isCancelStep, 'bi-x-circle-fill' => $isCancelStep, 'bi-circle' => ! $time]) aria-hidden="true"></i>
                                <div>
                                    <div class="fw-medium">{{ $title }}</div>
                                    @if ($time && $title !== 'Hari acara')
                                        <div class="small text-body-secondary">{{ tanggal_indo($time, false, true) }}</div>
                                    @endif
                                    @if ($note)
                                        <div class="small text-body-secondary">{{ $note }}</div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </x-card>
            </div>
        </div>
    </div>
@endsection
