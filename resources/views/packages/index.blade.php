@extends('layouts.app')

@section('title', 'Paket Pernikahan')
@section('description', 'Daftar paket pernikahan Restikey beserta harga dan layanan yang termasuk.')

@section('content')
    <div class="container">
        <h1 class="section-title">Paket pernikahan</h1>
        <p class="section-lead mb-5">
            Setiap paket sudah termasuk layanan yang tercantum. Layanan tambahan seperti MC, dokumentasi ekstra,
            atau katering tambahan bisa dipilih saat booking.
        </p>

        <div class="row g-4">
            @forelse ($packages as $package)
                <div class="col-md-6 col-lg-4">
                    @include('packages._card', ['popular' => false])
                </div>
            @empty
                <p class="text-body-secondary">Paket sedang disiapkan. Silakan kembali lagi nanti.</p>
            @endforelse
        </div>
    </div>
@endsection
