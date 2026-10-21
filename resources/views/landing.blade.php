@extends('layouts.app')

@section('main-class', '')

@php
    // Form booking dibuat di Phase 4; sampai saat itu tombol booking mengarah ke pendaftaran
    $bookingUrl = fn (array $params = []) => Route::has('bookings.create') ? route('bookings.create', $params) : route('register');
@endphp

@section('content')
    {{-- Hero --}}
    <section class="hero">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <h1>Hari bahagia Anda, kami yang mengurus semuanya.</h1>
                    <p class="lead mb-4">
                        Restikey adalah wedding organizer yang menyiapkan pernikahan Anda dari dekorasi,
                        tata rias, dokumentasi, sampai koordinasi hari H. Pilih paket, cek tanggal,
                        dan pesan langsung dari website ini.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#paket" class="btn btn-primary btn-lg">Lihat paket</a>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">Daftar akun</a>
                        @endguest
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="invitation" aria-hidden="true">
                        <i class="bi bi-flower3 ornament"></i>
                        <p class="invitation-intro">Dengan memohon rahmat Tuhan,<br>kami mengundang Anda di hari bahagia</p>
                        <span class="blank blank-name">nama Anda</span>
                        <div class="invitation-amp">&amp;</div>
                        <span class="blank blank-name">nama pasangan</span>
                        <span class="blank blank-date">tanggal pilihan Anda</span>
                        <span class="blank">lokasi acara</span>
                        <p class="invitation-signoff">Diatur dengan sepenuh hati oleh {{ config('app.name') }}</p>
                    </div>
                    <p class="text-center small text-body-secondary mt-4 mb-0">
                        Tanggal acara bisa dipesan mulai {{ config('restikey.min_booking_days') }} hari dari hari ini.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Layanan --}}
    <section id="layanan" class="section section-blush">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-5">
                    <h2 class="section-title">Satu tim untuk seluruh pernikahan Anda</h2>
                    <p class="section-lead">
                        Anda cukup menentukan konsep dan tanggal. Vendor, jadwal, dan kru hari H kami yang
                        koordinasikan, jadi Anda dan keluarga bisa menikmati acaranya.
                    </p>
                </div>
                <div class="col-lg-7">
                    @foreach ([
                        ['flower2', 'Dekorasi & pelaminan', 'Konsep dekorasi, pelaminan, dan area foto sesuai tema pilihan Anda.'],
                        ['brush', 'Tata rias & busana', 'Rias pengantin dan busana untuk akad maupun resepsi.'],
                        ['camera', 'Dokumentasi', 'Foto dan video sepanjang acara, hingga video cinematic.'],
                        ['mic', 'MC & hiburan', 'MC berpengalaman, sound system, dan live music.'],
                        ['cup-hot', 'Katering', 'Prasmanan untuk tamu, bisa ditambah sesuai jumlah undangan.'],
                        ['clipboard-check', 'Koordinasi hari H', 'Technical meeting, rundown, dan kru yang memastikan acara berjalan tepat waktu.'],
                    ] as [$icon, $title, $text])
                        <div class="service-item">
                            <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
                            <div>
                                <h3>{{ $title }}</h3>
                                <p>{{ $text }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Paket --}}
    <section id="paket" class="section">
        <div class="container">
            <h2 class="section-title">Paket pernikahan</h2>
            <p class="section-lead mb-5">
                Setiap paket sudah termasuk layanan di bawah ini. Layanan tambahan bisa dipilih saat booking.
            </p>

            <div class="row g-4">
                @forelse ($packages as $package)
                    <div class="col-md-6 col-lg-4">
                        @include('packages._card', ['popular' => $package->id === $popularId])
                    </div>
                @empty
                    <p class="text-body-secondary">Paket sedang disiapkan. Silakan kembali lagi nanti.</p>
                @endforelse
            </div>

            @if ($addons->isNotEmpty())
                <h3 class="mt-5 mb-3">Layanan tambahan</h3>
                <div class="addon-list">
                    @foreach ($addons as $addon)
                        <div title="{{ $addon->description }}">
                            <span>{{ $addon->name }}</span>
                            <span>{{ rupiah($addon->price) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Cara booking --}}
    <section id="cara-booking" class="section section-blush">
        <div class="container">
            <h2 class="section-title mb-5">Cara booking</h2>
            <ol class="steps">
                <li>
                    <h3>Pilih paket & tanggal</h3>
                    <p>Tanggal acara minimal {{ config('restikey.min_booking_days') }} hari dari hari pemesanan.</p>
                </li>
                <li>
                    <h3>Kirim booking</h3>
                    <p>Isi nama mempelai, lokasi, jumlah tamu, dan layanan tambahan.</p>
                </li>
                <li>
                    <h3>Tunggu konfirmasi</h3>
                    <p>Tim kami memeriksa jadwal lalu mengonfirmasi booking Anda.</p>
                </li>
                <li>
                    <h3>Bayar DP {{ config('restikey.dp_percentage') }}%</h3>
                    <p>Transfer dan unggah bukti dalam {{ config('restikey.auto_cancel_days') }} hari setelah konfirmasi agar tanggal tetap milik Anda.</p>
                </li>
                <li>
                    <h3>Lunasi sebelum H-{{ config('restikey.payment_deadline_days') }}</h3>
                    <p>Setelah lunas, tinggal menikmati hari bahagia Anda.</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- Portofolio --}}
    @if ($portfolios->isNotEmpty())
        <section id="portofolio" class="section">
            <div class="container">
                <h2 class="section-title">Pernikahan yang sudah kami tangani</h2>
                <p class="section-lead mb-5">Sebagian momen dari pasangan yang mempercayakan hari bahagianya kepada kami.</p>

                <div class="portfolio-grid">
                    @foreach ($portfolios as $portfolio)
                        <figure>
                            <img src="{{ $portfolio->image_url }}" alt="{{ $portfolio->title }}" loading="lazy">
                            <figcaption>
                                <strong>{{ $portfolio->title }}</strong>
                                {{ $portfolio->package ? 'Paket '.$portfolio->package->name.', ' : '' }}{{ tanggal_indo($portfolio->event_date) }}
                            </figcaption>
                        </figure>
                    @endforeach
                </div>

                <a href="{{ route('portfolios.index') }}" class="btn btn-outline-primary mt-4">Lihat semua portofolio</a>
            </div>
        </section>
    @endif

    {{-- Testimoni --}}
    @if ($reviews->isNotEmpty())
        <section class="section section-blush">
            <div class="container">
                <h2 class="section-title text-center mb-4">Kata mereka</h2>
                <div id="testimonials" class="carousel slide testimonial-carousel" data-bs-ride="false">
                    <div class="carousel-inner">
                        @foreach ($reviews as $review)
                            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                <figure class="testimonial">
                                    <div class="stars mb-3" aria-label="Rating {{ $review->rating }} dari 5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}" aria-hidden="true"></i>
                                        @endfor
                                    </div>
                                    <blockquote>{{ $review->comment }}</blockquote>
                                    <figcaption>
                                        {{ $review->user->name }}, paket {{ $review->booking->package->name }}
                                    </figcaption>
                                </figure>
                            </div>
                        @endforeach
                    </div>
                    @if ($reviews->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#testimonials" data-bs-slide="prev">
                            <i class="bi bi-chevron-left fs-3" aria-hidden="true"></i>
                            <span class="visually-hidden">Sebelumnya</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#testimonials" data-bs-slide="next">
                            <i class="bi bi-chevron-right fs-3" aria-hidden="true"></i>
                            <span class="visually-hidden">Berikutnya</span>
                        </button>
                        <div class="carousel-indicators">
                            @foreach ($reviews as $review)
                                <button type="button" data-bs-target="#testimonials" data-bs-slide-to="{{ $loop->index }}"
                                        class="{{ $loop->first ? 'active' : '' }}" aria-label="Ulasan {{ $loop->iteration }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- Ajakan penutup --}}
    <section class="section cta-band">
        <div class="container text-center">
            <h2 class="section-title">Sudah punya tanggal impian?</h2>
            <p class="mx-auto mb-4">
                Satu tanggal hanya untuk satu pasangan. Begitu booking lain dikonfirmasi, tanggal itu tidak bisa dipesan lagi.
            </p>
            @if (Route::has('availability.index'))
                <a href="{{ route('availability.index') }}" class="btn btn-secondary btn-lg">Cek ketersediaan tanggal</a>
            @else
                <a href="{{ $bookingUrl() }}" class="btn btn-secondary btn-lg">Booking sekarang</a>
            @endif
        </div>
    </section>
@endsection
