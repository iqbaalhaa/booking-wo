{{-- Lembar Excel: kop + tabel laporan. Dibaca oleh Maatwebsite\Excel (FromView) --}}
@php($colspan = $report === 'bookings' ? 7 : 6)
<table>
    <tr><td colspan="{{ $colspan }}"><strong>{{ config('app.name') }} Wedding Organizer</strong></td></tr>
    <tr><td colspan="{{ $colspan }}"><strong>{{ $title }}</strong></td></tr>
    <tr><td colspan="{{ $colspan }}">@include('admin.reports._period')</td></tr>
    <tr><td colspan="{{ $colspan }}">Dicetak {{ tanggal_indo(now(), false, true) }}</td></tr>
    <tr><td colspan="{{ $colspan }}"></td></tr>
</table>

@include($report === 'bookings' ? 'admin.reports._bookings-table' : 'admin.reports._payments-table', ['excel' => true])
