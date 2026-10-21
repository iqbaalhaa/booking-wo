<x-mail::message>
# Bukti pembayaran perlu diunggah ulang

Halo {{ $booking->user->name }}, bukti pembayaran **{{ $payment->typeLabel() }}** sebesar {{ rupiah($payment->amount) }} untuk booking {{ $booking->booking_code }} belum bisa kami verifikasi.

<x-mail::panel>
**Alasan:** {{ $payment->rejection_reason }}
</x-mail::panel>

Silakan periksa kembali transfer Anda lalu unggah bukti yang benar.

<x-mail::button :url="route('payments.create', $booking)">
Unggah ulang bukti
</x-mail::button>

Salam,<br>
Tim {{ config('app.name') }}
</x-mail::message>
