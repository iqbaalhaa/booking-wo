@extends('layouts.app')

@section('title', 'Paket '.$package->name)
@section('description', \Illuminate\Support\Str::limit($package->description ?? 'Paket pernikahan '.$package->name.' dari Restikey.', 155))

@php
    $bookingUrl = Route::has('bookings.create')
        ? route('bookings.create', ['package' => $package->slug])
        : route('register');
@endphp

@section('content')
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('packages.index') }}">Paket</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $package->name }}</li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-lg-7">
                @if ($package->thumbnail_url)
                    <img src="{{ $package->thumbnail_url }}" alt="Contoh dekorasi paket {{ $package->name }}"
                         class="img-fluid rounded-3 w-100 object-fit-cover mb-4" style="aspect-ratio: 16 / 10">
                @endif

                <h1 class="display-5 mb-2">Paket {{ $package->name }}</h1>
                @if ($package->description)
                    <p class="lead section-lead">{{ $package->description }}</p>
                @endif

                <h2 class="h3 mt-5 mb-3">Yang termasuk dalam paket</h2>
                <ul class="feature-list">
                    @foreach ($package->features as $feature)
                        <li><i class="bi bi-check2-circle" aria-hidden="true"></i> {{ $feature->feature }}</li>
                    @endforeach
                </ul>

                @if ($addons->isNotEmpty())
                    <h2 class="h3 mt-5 mb-1">Layanan tambahan</h2>
                    <p class="text-body-secondary">Bisa dipilih saat booking dan ditambahkan ke total harga.</p>
                    <div class="list-group">
                        @foreach ($addons as $addon)
                            <div class="list-group-item d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-medium">{{ $addon->name }}</div>
                                    @if ($addon->description)
                                        <div class="small text-body-secondary">{{ $addon->description }}</div>
                                    @endif
                                </div>
                                <div class="text-nowrap fw-medium">{{ rupiah($addon->price) }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-5">
                <div class="card package-summary">
                    <div class="card-body p-4">
                        <div class="package-price">{{ rupiah($package->price) }}</div>
                        <div class="text-body-secondary mb-4">Hingga {{ number_format($package->max_guests, 0, ',', '.') }} tamu</div>

                        <dl class="small mb-4">
                            <dt>Uang muka (DP)</dt>
                            <dd>Minimal {{ config('restikey.dp_percentage') }}% dari total, mulai {{ rupiah(ceil($package->price * config('restikey.dp_percentage') / 100)) }}</dd>
                            <dt>Pelunasan</dt>
                            <dd>Paling lambat H-{{ config('restikey.payment_deadline_days') }} sebelum acara</dd>
                            <dt>Tanggal acara</dt>
                            <dd class="mb-0">Minimal {{ config('restikey.min_booking_days') }} hari dari hari booking</dd>
                        </dl>

                        <a href="{{ $bookingUrl }}" class="btn btn-primary btn-lg w-100">Booking paket ini</a>
                        @if (Route::has('availability.index'))
                            <a href="{{ route('availability.index') }}" class="btn btn-link w-100 mt-2">Cek ketersediaan tanggal dulu</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($portfolios->isNotEmpty())
            <section class="mt-5 pt-4">
                <h2 class="h3 mb-3">Pernikahan dengan paket {{ $package->name }}</h2>
                <div class="row g-3">
                    @foreach ($portfolios as $portfolio)
                        <div class="col-md-4">
                            <figure class="portfolio-tile mb-0">
                                <img src="{{ $portfolio->image_url }}" alt="{{ $portfolio->title }}" loading="lazy">
                                <figcaption><strong>{{ $portfolio->title }}</strong>{{ tanggal_indo($portfolio->event_date) }}</figcaption>
                            </figure>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('portfolios.index', ['paket' => $package->slug]) }}" class="btn btn-outline-primary mt-3">Lihat semua portofolio paket ini</a>
            </section>
        @endif

        @if ($reviews->isNotEmpty())
            <section class="mt-5 pt-4">
                <h2 class="h3 mb-3">Kata pasangan yang memilih paket ini</h2>
                <div class="row g-3">
                    @foreach ($reviews as $review)
                        <div class="col-md-6">
                            <x-card class="h-100">
                                <div class="rating-stars mb-2" aria-label="Rating {{ $review->rating }} dari 5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}" aria-hidden="true"></i>
                                    @endfor
                                </div>
                                <p class="mb-2">{{ $review->comment }}</p>
                                <div class="small text-body-secondary">{{ $review->user->name }}</div>
                            </x-card>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
