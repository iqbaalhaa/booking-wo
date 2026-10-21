<x-mail::message>
@if ($forAdmin)
# Booking baru masuk

**{{ $booking->user->name }}** ({{ $booking->user->email }}, {{ $booking->user->phone ?? 'tanpa nomor telepon' }}) mengajukan booking dan menunggu konfirmasi.
@else
# Terima kasih, {{ $booking->user->name }}

Booking Anda sudah kami terima. Tim kami akan memeriksa jadwal lalu mengonfirmasi booking Anda.
@endif

<x-mail::panel>
**Kode booking:** {{ $booking->booking_code }}<br>
**Mempelai:** {{ $booking->groom_name }} & {{ $booking->bride_name }}<br>
**Tanggal acara:** {{ tanggal_indo($booking->event_date, true) }}<br>
**Lokasi:** {{ $booking->event_location }}<br>
**Paket:** {{ $booking->package->name }}<br>
**Total:** {{ rupiah($booking->total_price) }}
</x-mail::panel>

@if ($forAdmin)
<x-mail::button :url="route('admin.bookings.show', $booking)">
Tinjau booking
</x-mail::button>
@else
Setelah booking dikonfirmasi, Anda perlu membayar DP minimal **{{ rupiah($booking->minimumDp()) }}** dalam {{ config('restikey.auto_cancel_days') }} hari.

<x-mail::button :url="route('bookings.show', $booking)">
Lihat booking
</x-mail::button>
@endif

Salam,<br>
Tim {{ config('app.name') }}
</x-mail::message>
