{{-- Tabel laporan pembayaran terverifikasi. $rows, $total; $excel = true → angka mentah --}}
@php($excel ??= false)
<table class="table table-sm align-middle mb-0 report-table">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal verifikasi</th>
            <th>Kode booking</th>
            <th>Customer</th>
            <th>Jenis</th>
            <th class="text-end">Nominal</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $payment)
            <tr>
                <td>{{ method_exists($rows, 'firstItem') ? $rows->firstItem() + $loop->index : $loop->iteration }}</td>
                <td>{{ tanggal_indo($payment->verified_at, false, true) }}</td>
                <td>{{ $payment->booking->booking_code }}</td>
                <td>{{ $payment->booking->user->name }}</td>
                <td>{{ $payment->typeLabel() }}</td>
                <td class="text-end">{{ $excel ? $payment->amount : rupiah($payment->amount) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-body-secondary py-3">Tidak ada pembayaran terverifikasi pada periode ini.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" class="text-end">Total diterima</th>
            <th class="text-end">{{ $excel ? $total : rupiah($total) }}</th>
        </tr>
    </tfoot>
</table>
