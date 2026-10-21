@extends('layouts.app')

@use('App\Models\Payment')

@section('title', 'Pembayaran '.$booking->booking_code)

@section('content')
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Booking saya</a></li>
                <li class="breadcrumb-item"><a href="{{ route('bookings.show', $booking) }}">{{ $booking->booking_code }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pembayaran</li>
            </ol>
        </nav>

        <h1 class="mb-1">{{ $type === Payment::TYPE_DP ? 'Bayar DP' : 'Bayar pelunasan' }}</h1>
        <p class="text-body-secondary mb-4">
            Transfer ke rekening di bawah, lalu unggah bukti transfernya. Tim kami akan mengecek dan memverifikasi pembayaran Anda.
        </p>

        @if ($pastDeadline)
            <x-alert type="warning">
                Batas pelunasan ({{ tanggal_indo($booking->paymentDeadline(), true) }}) sudah lewat. Segera lakukan pelunasan dan hubungi tim kami.
            </x-alert>
        @endif

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card bank-card mb-4">
                    <div class="card-body p-4">
                        <div class="small text-body-secondary">Transfer ke rekening</div>
                        <div class="fs-5 fw-medium mt-1">{{ config('restikey.bank.name') }}</div>
                        <div class="d-flex align-items-center gap-2 my-1">
                            <span class="bank-number">{{ config('restikey.bank.account_number') }}</span>
                            <button type="button" class="btn btn-outline-primary btn-sm" data-copy="{{ config('restikey.bank.account_number') }}">
                                <i class="bi bi-copy" aria-hidden="true"></i> <span data-copy-label aria-live="polite">Salin</span>
                            </button>
                        </div>
                        <div>a.n. {{ config('restikey.bank.account_name') }}</div>
                    </div>
                </div>

                <x-card title="Tagihan">
                    <dl class="summary-lines mb-0">
                        <dt>Total biaya</dt><dd>{{ rupiah($booking->total_price) }}</dd>
                        <dt>Sudah dibayar</dt><dd>{{ rupiah($booking->verifiedTotal()) }}</dd>
                        <dt class="fw-semibold">Sisa tagihan</dt><dd class="fw-semibold">{{ rupiah($booking->remainingAmount()) }}</dd>
                        @if ($type === Payment::TYPE_DP)
                            <dt>DP minimal ({{ config('restikey.dp_percentage') }}%)</dt><dd>{{ rupiah($booking->minimumDp()) }}</dd>
                            <dt>Batas DP</dt><dd>{{ tanggal_indo($booking->dpDeadline(), true) }}</dd>
                        @else
                            <dt>Batas pelunasan</dt><dd>{{ tanggal_indo($booking->paymentDeadline(), true) }}</dd>
                        @endif
                    </dl>
                </x-card>
            </div>

            <div class="col-lg-7">
                <form method="POST" action="{{ route('payments.store', $booking) }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">

                    <x-card title="Konfirmasi transfer">
                        @error('type')<x-alert type="error">{{ $message }}</x-alert>@enderror

                        <div class="mb-3">
                            <label for="amount" class="form-label">Nominal yang ditransfer</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">Rp</span>
                                <input id="amount" name="amount" inputmode="numeric" aria-describedby="amount-help"
                                       value="{{ old('amount', number_format($suggestedAmount, 0, ',', '.')) }}"
                                       class="form-control @error('amount') is-invalid @enderror" required>
                                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div id="amount-help" class="form-text">
                                @if ($type === Payment::TYPE_DP)
                                    Minimal {{ rupiah($booking->minimumDp()) }}. Boleh lebih, maksimal sebesar sisa tagihan.
                                @else
                                    Sisa tagihan {{ rupiah($booking->remainingAmount()) }}.
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-5">
                                <label for="bank_name" class="form-label">Bank pengirim</label>
                                <input id="bank_name" name="bank_name" value="{{ old('bank_name') }}" maxlength="50" placeholder="BCA, BRI, Mandiri…"
                                       class="form-control @error('bank_name') is-invalid @enderror" required>
                                @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-7">
                                <label for="account_name" class="form-label">Nama pemilik rekening pengirim</label>
                                <input id="account_name" name="account_name" value="{{ old('account_name', auth()->user()->name) }}" maxlength="100"
                                       class="form-control @error('account_name') is-invalid @enderror" required>
                                @error('account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="proof" class="form-label">Bukti transfer</label>
                            <input id="proof" name="proof" type="file" accept="image/jpeg,image/png,application/pdf"
                                   data-preview="#proof-preview" data-preview-name="#proof-name" aria-describedby="proof-help"
                                   class="form-control @error('proof') is-invalid @enderror" required>
                            <div id="proof-help" class="form-text">Foto/screenshot JPG atau PNG, atau PDF. Maksimal 2 MB.</div>
                            @error('proof')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div id="proof-name" class="small text-body-secondary mt-2"></div>
                            <img id="proof-preview" alt="Pratinjau bukti transfer" class="proof-preview mt-2" hidden>
                        </div>

                        <x-slot:footer>
                            <button type="submit" class="btn btn-primary w-100">Kirim bukti pembayaran</button>
                        </x-slot:footer>
                    </x-card>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/copy.js') }}"></script>
    <script src="{{ asset('js/image-preview.js') }}"></script>
@endpush
