@extends('layouts.app')

@section('title', 'Portofolio')
@section('description', 'Galeri pernikahan yang sudah ditangani Restikey Wedding Organizer.')

@section('content')
    <div class="container">
        <h1 class="section-title">Portofolio</h1>
        <p class="section-lead mb-4">Momen dari pasangan yang mempercayakan hari bahagianya kepada kami.</p>

        <nav class="nav nav-pills gap-2 mb-4" aria-label="Filter paket">
            <a @class(['nav-link', 'active' => ! $selected]) href="{{ route('portfolios.index') }}" @if (! $selected) aria-current="page" @endif>Semua</a>
            @foreach ($packages as $package)
                <a @class(['nav-link', 'active' => $selected?->is($package)]) href="{{ route('portfolios.index', ['paket' => $package->slug]) }}"
                   @if ($selected?->is($package)) aria-current="page" @endif>Paket {{ $package->name }}</a>
            @endforeach
        </nav>

        @if ($portfolios->isEmpty())
            <p class="text-body-secondary py-5 text-center">Belum ada portofolio untuk paket ini.</p>
        @else
            <div class="row g-3">
                @foreach ($portfolios as $portfolio)
                    <div class="col-6 col-md-4 col-lg-3">
                        <button type="button" class="portfolio-tile" data-bs-toggle="modal" data-bs-target="#lightbox" data-slide="{{ $loop->index }}"
                                aria-label="Buka foto {{ $portfolio->title }}">
                            <img src="{{ $portfolio->image_url }}" alt="" loading="lazy">
                            <span class="portfolio-tile-caption"><strong>{{ $portfolio->title }}</strong>{{ tanggal_indo($portfolio->event_date) }}</span>
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">{{ $portfolios->links() }}</div>
        @endif
    </div>
@endsection

@if ($portfolios->isNotEmpty())
    @push('modals')
        <div class="modal fade" id="lightbox" tabindex="-1" aria-label="Galeri portofolio" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-dark border-0">
                    <div class="modal-header border-0 py-2">
                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="carousel slide" id="lightbox-carousel">
                        <div class="carousel-inner">
                            @foreach ($portfolios as $portfolio)
                                <div class="carousel-item">
                                    <img src="{{ $portfolio->image_url }}" alt="{{ $portfolio->title }}" class="lightbox-image" loading="lazy">
                                    <div class="lightbox-caption">
                                        <h2 class="h4 mb-1">{{ $portfolio->title }}</h2>
                                        <div class="small">
                                            {{ $portfolio->package ? 'Paket '.$portfolio->package->name.', ' : '' }}{{ tanggal_indo($portfolio->event_date) }}
                                        </div>
                                        @if ($portfolio->description)
                                            <p class="small mb-0 mt-2">{{ $portfolio->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($portfolios->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#lightbox-carousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Sebelumnya</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#lightbox-carousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Berikutnya</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endpush

    @push('scripts')
        <script src="{{ asset('js/lightbox.js') }}"></script>
    @endpush
@endif
