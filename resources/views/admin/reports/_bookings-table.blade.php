{{-- Tabel laporan booking. $rows, $total; $excel = true → angka mentah agar bisa dijumlah di Excel --}}
@php($excel ??= false)
<table class="table table-sm align-middle mb-0 report-table">
    <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Tanggal acara</th>
            <th>Customer</th>
            <th>Paket</th>
            <th class="text-end">Total</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $booking)
            <tr>
                <td>{{ method_exists($rows, 'firstItem') ? $rows->firstItem() + $loop->index : $loop->iteration }}</td>
                <td>{{ $booking->booking_code }}</td>
                <td>{{ tanggal_indo($booking->event_date) }}</td>
                <td>{{ $booking->user->name }}</td>
                <td>{{ $booking->package->name }}</td>
                <td class="text-end">{{ $excel ? $booking->total_price : rupiah($booking->total_price) }}</td>
                <td>{{ $booking->statusLabel() }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-body-secondary py-3">Tidak ada booking pada periode ini.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" class="text-end">Total keseluruhan</th>
            <th class="text-end">{{ $excel ? $total : rupiah($total) }}</th>
            <th></th>
        </tr>
    </tfoot>
</table>
