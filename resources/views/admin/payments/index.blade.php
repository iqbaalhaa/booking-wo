@extends('layouts.admin')

@section('title', 'Pembayaran')

@section('content')
    <nav class="nav nav-pills gap-2 mb-3" aria-label="Filter status pembayaran">
        @foreach (\App\Models\Payment::STATUSES as $key => $label)
            <a @class(['nav-link', 'active' => $status === $key]) href="{{ route('admin.payments.index', ['status' => $key]) }}"
               @if ($status === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
        <a @class(['nav-link', 'active' => ! $status]) href="{{ route('admin.payments.index', ['status' => 'semua']) }}">Semua</a>
    </nav>

    <x-card body-class="p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Diunggah</th>
                        <th>Booking</th>
                        <th>Jenis</th>
                        <th class="text-end">Nominal</th>
                        <th>Pengirim</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="text-nowrap small">{{ tanggal_indo($payment->created_at, false, true) }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $payment->booking) }}" class="fw-medium">{{ $payment->booking->booking_code }}</a>
                                <div class="small text-body-secondary">{{ $payment->booking->user->name }}</div>
                            </td>
                            <td>{{ $payment->typeLabel() }}</td>
                            <td class="text-end text-nowrap">{{ rupiah($payment->amount) }}</td>
                            <td class="small">{{ $payment->account_name }}<div class="text-body-secondary">{{ $payment->bank_name }}</div></td>
                            <td>
                                <x-badge-status :status="$payment->status" type="payment" />
                                @if ($payment->verifier)
                                    <div class="small text-body-secondary mt-1">oleh {{ $payment->verifier->name }}</div>
                                @endif
                                @if ($payment->rejection_reason)
                                    <div class="small text-danger">{{ $payment->rejection_reason }}</div>
                                @endif
                            </td>
                            <td>@include('admin.payments._actions')</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-body-secondary py-4">
                            {{ $status === 'pending' ? 'Tidak ada pembayaran yang menunggu verifikasi.' : 'Tidak ada pembayaran.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <x-slot:footer>{{ $payments->links() }}</x-slot:footer>
        @endif
    </x-card>
@endsection
