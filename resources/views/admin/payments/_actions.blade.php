{{-- Tombol lihat bukti (modal) + verifikasi/tolak untuk satu pembayaran. Variabel: $payment (dengan booking) --}}
@php($modalId = 'proof-'.$payment->id)

<div class="d-flex flex-wrap gap-1 justify-content-end">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
        <i class="bi bi-receipt" aria-hidden="true"></i> Bukti
    </button>

    @if ($payment->status === \App\Models\Payment::STATUS_PENDING)
        <x-confirm-form :action="route('admin.payments.verify', $payment)" title="Verifikasi pembayaran?"
                        :message="'Pastikan dana '.rupiah($payment->amount).' dari '.$payment->account_name.' ('.$payment->bank_name.') sudah masuk ke rekening '.config('restikey.bank.name').'.'"
                        confirm-label="Ya, dana sudah masuk" confirm-class="btn-success" button-class="btn btn-success btn-sm">
            Verifikasi
        </x-confirm-form>
        <x-confirm-form :action="route('admin.payments.reject', $payment)" title="Tolak pembayaran?"
                        message="Customer akan diminta mengunggah ulang bukti transfer."
                        reason="rejection_reason" reason-label="Alasan penolakan (dikirim ke customer)" confirm-label="Tolak">
            Tolak
        </x-confirm-form>
    @endif
</div>

@push('modals')
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="{{ $modalId }}-title">
                        Bukti {{ $payment->typeLabel() }} · {{ $payment->booking->booking_code }}
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <dl class="row small">
                        <dt class="col-sm-4">Nominal</dt><dd class="col-sm-8 fw-semibold">{{ rupiah($payment->amount) }}</dd>
                        <dt class="col-sm-4">Pengirim</dt><dd class="col-sm-8">{{ $payment->account_name }} ({{ $payment->bank_name }})</dd>
                        <dt class="col-sm-4">Diunggah</dt><dd class="col-sm-8 mb-0">{{ tanggal_indo($payment->created_at, true, true) }}</dd>
                    </dl>
                    @if ($payment->isPdf())
                        <iframe src="{{ $payment->proof_url }}" title="Bukti transfer PDF" class="proof-frame" loading="lazy"></iframe>
                    @else
                        <img src="{{ $payment->proof_url }}" alt="Bukti transfer" class="img-fluid rounded border" loading="lazy">
                    @endif
                </div>
                <div class="modal-footer">
                    <a href="{{ $payment->proof_url }}" target="_blank" rel="noopener" class="btn btn-light">Buka di tab baru</a>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endpush
