{{-- <x-badge-status :status="$booking->status" /> atau <x-badge-status :status="$payment->status" type="payment" /> --}}
@props(['status', 'type' => 'booking'])

@php
    [$colors, $labels] = $type === 'payment'
        ? [
            ['pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger'],
            \App\Models\Payment::STATUSES,
        ]
        : [
            ['pending' => 'warning', 'confirmed' => 'info', 'paid' => 'primary', 'completed' => 'success', 'cancelled' => 'light border'],
            \App\Models\Booking::STATUSES,
        ];
@endphp

<span {{ $attributes->merge(['class' => 'badge rounded-pill text-bg-'.($colors[$status] ?? 'light border')]) }}>
    {{ $labels[$status] ?? $status }}
</span>
