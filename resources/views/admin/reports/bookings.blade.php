@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
    @include('admin.reports._header', ['active' => 'bookings'])

    <x-card :title="'Booking '.tanggal_indo($filters['from']).' – '.tanggal_indo($filters['to']).' ('.($filters['basis'] === 'created_at' ? 'tanggal dibuat' : 'tanggal acara').')'" body-class="p-0">
        <x-slot:actions>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.bookings.export', ['format' => 'excel', ...request()->query()]) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Excel
                </a>
                <a href="{{ route('admin.reports.bookings.export', ['format' => 'pdf', ...request()->query()]) }}" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> PDF
                </a>
            </div>
        </x-slot:actions>

        <div class="d-flex flex-wrap gap-4 px-3 py-2 border-bottom small">
            <span>Jumlah booking: <strong>{{ $count }}</strong></span>
            <span>Total nilai: <strong>{{ rupiah($total) }}</strong></span>
        </div>

        <div class="table-responsive">
            @include('admin.reports._bookings-table')
        </div>

        @if ($rows->hasPages())
            <x-slot:footer>{{ $rows->links() }}</x-slot:footer>
        @endif
    </x-card>
@endsection
