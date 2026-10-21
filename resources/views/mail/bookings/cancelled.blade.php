<x-mail::message>
# Booking dibatalkan

Halo {{ $booking->user->name }}, booking **{{ $booking->booking_code }}** untuk tanggal {{ tanggal_indo($booking->event_date, true) }} telah dibatalkan.

@if ($booking->cancel_reason)
<x-mail::panel>
**Alasan:** {{ $booking->cancel_reason }}
</x-mail::panel>
@endif

Jika Anda sudah melakukan pembayaran untuk booking ini, tim kami akan menghubungi Anda untuk proses selanjutnya. Anda juga bisa memilih tanggal lain kapan saja.

<x-mail::button :url="route('availability.index')">
Cek tanggal lain
</x-mail::button>

Salam,<br>
Tim {{ config('app.name') }}
</x-mail::message>
