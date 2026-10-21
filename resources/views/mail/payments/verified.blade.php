<x-mail::message>
# Pembayaran diterima

Halo {{ $booking->user->name }}, pembayaran **{{ $payment->typeLabel() }}** sebesar **{{ rupiah($payment->amount) }}** untuk booking {{ $booking->booking_code }} sudah kami verifikasi.

<x-mail::panel>
**Total biaya:** {{ rupiah($booking->total_price) }}<br>
**Sudah dibayar:** {{ rupiah($booking->verifiedTotal()) }}<br>
**Sisa tagihan:** {{ rupiah($booking->remainingAmount()) }}
</x-mail::panel>

@if ($booking->remainingAmount() > 0)
Mohon lunasi sisa tagihan paling lambat **{{ tanggal_indo($booking->paymentDeadline(), true) }}** (H-{{ config('restikey.payment_deadline_days') }} sebelum acara).
@else
Pembayaran Anda sudah **lunas**. Sampai jumpa di hari bahagia Anda!
@endif

<x-mail::button :url="route('bookings.show', $booking)">
Lihat booking
</x-mail::button>

Salam,<br>
Tim {{ config('app.name') }}
</x-mail::message>
