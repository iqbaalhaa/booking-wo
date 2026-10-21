<x-mail::message>
# Booking Anda dikonfirmasi

Halo {{ $booking->user->name }}, tanggal **{{ tanggal_indo($booking->event_date, true) }}** sudah kami siapkan untuk pernikahan {{ $booking->groom_name }} & {{ $booking->bride_name }}.

Agar tanggal tetap menjadi milik Anda, bayar DP minimal **{{ rupiah($booking->minimumDp()) }}** paling lambat **{{ tanggal_indo($booking->dpDeadline(), true, true) }}**. Jika DP belum masuk sampai batas tersebut, booking dibatalkan otomatis.

<x-mail::panel>
**Transfer ke:**<br>
{{ config('restikey.bank.name') }} {{ config('restikey.bank.account_number') }}<br>
a.n. {{ config('restikey.bank.account_name') }}

**Kode booking:** {{ $booking->booking_code }}<br>
**Total biaya:** {{ rupiah($booking->total_price) }}<br>
**Pelunasan paling lambat:** {{ tanggal_indo($booking->paymentDeadline(), true) }}
</x-mail::panel>

Setelah transfer, unggah bukti pembayaran melalui tombol di bawah. Tim kami akan mengecek dan memverifikasinya.

<x-mail::button :url="route('payments.create', $booking)">
Unggah bukti DP
</x-mail::button>

Salam,<br>
Tim {{ config('app.name') }}
</x-mail::message>
