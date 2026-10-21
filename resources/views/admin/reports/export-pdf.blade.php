<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    {{-- Dompdf hanya membaca CSS di dokumen ini; Bootstrap tidak dimuat --}}
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #3E2C2E; }
        .kop { border-bottom: 2px solid #9E5560; padding-bottom: 8px; margin-bottom: 12px; }
        .kop h1 { font-size: 18px; margin: 0; color: #9E5560; }
        .kop h2 { font-size: 13px; margin: 4px 0 0; }
        .kop p { margin: 2px 0 0; color: #6b5a5c; }
        table.report-table { width: 100%; border-collapse: collapse; }
        .report-table th, .report-table td { border: 1px solid #d8c9c6; padding: 5px 6px; text-align: left; }
        .report-table thead th { background: #F3E4E1; }
        .report-table tfoot th { background: #F3E4E1; }
        .text-end { text-align: right !important; }
        .text-center { text-align: center; }
        .footer { margin-top: 12px; color: #6b5a5c; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>{{ config('app.name') }} Wedding Organizer</h1>
        <h2>{{ $title }}</h2>
        <p>@include('admin.reports._period')</p>
    </div>

    @include($report === 'bookings' ? 'admin.reports._bookings-table' : 'admin.reports._payments-table')

    <p class="footer">Dicetak {{ tanggal_indo(now(), false, true) }} · {{ $rows->count() }} baris</p>
</body>
</html>
