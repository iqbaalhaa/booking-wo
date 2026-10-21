{{--
    Flash message Bootstrap yang bisa ditutup.
    Tanpa slot: tampilkan session flash success/status/error/warning/info.
    Dengan slot: <x-alert type="warning">Pesan</x-alert>
--}}
@props(['type' => 'info'])

@php
    $styles = [
        'success' => ['success', 'check-circle'],
        'status' => ['success', 'check-circle'],
        'error' => ['danger', 'exclamation-octagon'],
        'warning' => ['warning', 'exclamation-triangle'],
        'info' => ['info', 'info-circle'],
    ];

    $messages = $slot->isNotEmpty()
        ? [[$type, $slot]]
        : collect(array_keys($styles))->filter(fn ($key) => session()->has($key))->map(fn ($key) => [$key, session($key)])->all();
@endphp

@foreach ($messages as [$key, $message])
    <div {{ $attributes->merge(['class' => 'alert alert-'.$styles[$key][0].' alert-dismissible fade show d-flex gap-2']) }} role="alert">
        <i class="bi bi-{{ $styles[$key][1] }}" aria-hidden="true"></i>
        <div>{{ $message }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endforeach
