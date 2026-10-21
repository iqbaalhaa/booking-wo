@extends('layouts.admin')

@use('App\Models\Booking')

@section('title', 'Booking '.$booking->booking_code)

@section('content')
    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left" aria-hidden="true"></i> Daftar booking</a>
        <h2 class="h3 mb-0">{{ $booking->groom_name }} &amp; {{ $booking->bride_name }}</h2>
        <x-badge-status :status="$booking->status" class="fs-6" />
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <x-card title="Detail acara">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Tanggal acara</dt>
                    <dd class="col-sm-8">{{ tanggal_indo($booking->event_date, true) }}</dd>
                    <dt class="col-sm-4">Lokasi</dt>
                    <dd class="col-sm-8">{{ $booking->event_location }}</dd>
                    <dt class="col-sm-4">Paket</dt>
                    <dd class="col-sm-8">{{ $booking->package->name }}</dd>
                    <dt class="col-sm-4">Perkiraan tamu</dt>
                    <dd class="col-sm-8">{{ number_format($booking->guest_count, 0, ',', '.') }} orang</dd>
                    <dt class="col-sm-4">Catatan</dt>
                    <dd class="col-sm-8">{{ $booking->notes ?: '-' }}</dd>
                    <dt class="col-sm-4">Dibuat</dt>
                    <dd class="col-sm-8">{{ tanggal_indo($booking->created_at, true, true) }}</dd>
                    @if ($booking->confirmed_at)
                        <dt class="col-sm-4">Dikonfirmasi</dt>
                        <dd class="col-sm-8">{{ tanggal_indo($booking->confirmed_at, true, true) }}</dd>
                    @endif
                    @if ($booking->cancel_reason)
                        <dt class="col-sm-4">Alasan batal</dt>
                        <dd class="col-sm-8 text-danger mb-0">{{ $booking->cancel_reason }}</dd>
                    @endif
                </dl>
            </x-card>

            <x-card title="Rincian harga" class="mt-4" body-class="p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td>Paket {{ $booking->package->name }}</td><td class="text-end text-nowrap">{{ rupiah($booking->package_price) }}</td></tr>
                            @foreach ($booking->bookingAddons as $line)
                                <tr>
                                    <td>{{ $line->addon_name }} <span class="text-body-secondary">({{ $line->quantity }} × {{ rupiah($line->price) }})</span></td>
                                    <td class="text-end text-nowrap">{{ rupiah($line->subtotal()) }}</td>
                                </tr>
                            @endforeach
                            <tr class="fw-semibold"><td>Total</td><td class="text-end">{{ rupiah($booking->total_price) }}</td></tr>
                            <tr class="small"><td>DP minimal ({{ config('restikey.dp_percentage') }}%)</td><td class="text-end">{{ rupiah($booking->minimumDp()) }}</td></tr>
                            <tr class="small"><td>Terverifikasi</td><td class="text-end">{{ rupiah($booking->verifiedTotal()) }}</td></tr>
                            <tr class="small"><td>Sisa tagihan</td><td class="text-end">{{ rupiah($booking->remainingAmount()) }}</td></tr>
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
                                <tr><th>Diunggah</th><th>Jenis</th><th class="text-end">Nominal</th><th>Pengirim</th><th>Status</th><th class="text-end">Aksi</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($booking->payments as $payment)
                                    <tr>
                                        <td class="small text-nowrap">{{ tanggal_indo($payment->created_at, false, true) }}</td>
                                        <td>{{ $payment->typeLabel() }}</td>
                                        <td class="text-end text-nowrap">{{ rupiah($payment->amount) }}</td>
                                        <td class="small">{{ $payment->account_name }}<div class="text-body-secondary">{{ $payment->bank_name }}</div></td>
                                        <td>
                                            <x-badge-status :status="$payment->status" type="payment" />
                                            @if ($payment->verifier)
                                                <div class="small text-body-secondary mt-1">oleh {{ $payment->verifier->name }}, {{ tanggal_indo($payment->verified_at) }}</div>
                                            @endif
                                            @if ($payment->rejection_reason)
                                                <div class="small text-danger">{{ $payment->rejection_reason }}</div>
                                            @endif
                                        </td>
                                        <td>@include('admin.payments._actions')</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>

        <div class="col-xl-4">
            <x-card title="Aksi">
                @if ($booking->status === Booking::STATUS_PENDING)
                    @if ($dateTaken)
                        <x-alert type="warning">
                            Tanggal ini sudah dipakai booking <a href="{{ route('admin.bookings.show', $dateTaken) }}">{{ $dateTaken->booking_code }}</a>. Booking ini tidak bisa dikonfirmasi.
                        </x-alert>
                    @else
                        @if ($competing->isNotEmpty())
                            <p class="small">
                                Ada {{ $competing->count() }} booking pending lain di tanggal ini
                                ({{ $competing->map(fn ($other) => $other->booking_code.' oleh '.$other->user->name)->join(', ') }}).
                                Jika booking ini dikonfirmasi, booking tersebut dibatalkan otomatis.
                            </p>
                        @endif
                        <x-confirm-form :action="route('admin.bookings.confirm', $booking)" title="Konfirmasi booking?"
                                        :message="'Tanggal '.tanggal_indo($booking->event_date, true).' akan dikunci untuk booking ini dan customer menerima email instruksi DP.'
                                            .($competing->isNotEmpty() ? ' '.$competing->count().' booking pending lain di tanggal ini akan dibatalkan.' : '')"
                                        confirm-label="Konfirmasi" confirm-class="btn-primary" button-class="btn btn-primary w-100 mb-2">
                            Konfirmasi booking
                        </x-confirm-form>
                    @endif
                @endif

                @can('cancel', $booking)
                    <x-confirm-form :action="route('admin.bookings.cancel', $booking)"
                                    :title="$booking->status === Booking::STATUS_PENDING ? 'Tolak booking?' : 'Batalkan booking?'"
                                    message="Customer akan menerima email pembatalan beserta alasannya."
                                    reason="cancel_reason" reason-label="Alasan (dikirim ke customer)"
                                    :confirm-label="$booking->status === Booking::STATUS_PENDING ? 'Tolak booking' : 'Batalkan booking'"
                                    button-class="btn btn-outline-danger w-100 mb-2">
                        {{ $booking->status === Booking::STATUS_PENDING ? 'Tolak booking' : 'Batalkan booking' }}
                    </x-confirm-form>
                @endcan

                @if ($booking->status === Booking::STATUS_PAID)
                    @if ($booking->event_date->lt(today()))
                        <x-confirm-form :action="route('admin.bookings.complete', $booking)" title="Tandai selesai?"
                                        message="Customer bisa memberi ulasan setelah booking selesai."
                                        confirm-label="Tandai selesai" confirm-class="btn-success" button-class="btn btn-success w-100">
                            Tandai selesai
                        </x-confirm-form>
                    @else
                        <p class="small text-body-secondary mb-0">Booking lunas. Bisa ditandai selesai setelah {{ tanggal_indo($booking->event_date, true) }}.</p>
                    @endif
                @endif

                @if (in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED], true))
                    <p class="small text-body-secondary mb-0">Tidak ada aksi untuk booking {{ strtolower($booking->statusLabel()) }}.</p>
                @endif
            </x-card>

            <x-card title="Customer" class="mt-4">
                <div class="fw-medium">{{ $booking->user->name }}</div>
                <div class="small"><a href="mailto:{{ $booking->user->email }}">{{ $booking->user->email }}</a></div>
                @if ($booking->user->phone)
                    <div class="small">
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $booking->user->phone)) }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i> {{ $booking->user->phone }}
                        </a>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection
