{{-- Isi halaman error 403/404. Variabel: $code, $title, $message --}}
<div class="container">
    <div class="error-page">
        <div class="error-code" aria-hidden="true">{{ $code }}</div>
        <h1 class="mb-3">{{ $title }}</h1>
        <p class="section-lead mx-auto mb-4">{{ $message }}</p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="{{ route('landing') }}" class="btn btn-primary">Ke beranda</a>
            @auth
                <a href="{{ auth()->user()->homeUrl() }}" class="btn btn-outline-primary">
                    {{ auth()->user()->isAdmin() ? 'Ke panel admin' : 'Ke dashboard' }}
                </a>
            @else
                <a href="{{ route('availability.index') }}" class="btn btn-outline-primary">Cek tanggal</a>
            @endauth
        </div>
    </div>
</div>
