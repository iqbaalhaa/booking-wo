<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') | @endif{{ config('app.name') }} Wedding Organizer</title>
    <meta name="description" content="@yield('description', 'Restikey Wedding Organizer: paket pernikahan, cek ketersediaan tanggal, dan booking online.')">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,500i|jost:400,500" rel="stylesheet">

    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    {{-- ?v= agar browser memuat ulang CSS setelah file diubah --}}
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    @php
        // Menu muncul otomatis begitu route-nya dibuat di phase berikutnya
        $menu = array_filter([
            'Beranda' => route('landing'),
            'Paket' => Route::has('packages.index') ? route('packages.index') : route('landing').'#paket',
            'Portofolio' => Route::has('portfolios.index') ? route('portfolios.index') : route('landing').'#portofolio',
            'Cek Tanggal' => Route::has('availability.index') ? route('availability.index') : null,
            'Booking Saya' => auth()->user()?->isCustomer() && Route::has('bookings.index') ? route('bookings.index') : null,
        ]);
    @endphp

    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand brand-wordmark" href="{{ route('landing') }}">{{ config('app.name') }}</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Buka menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    @foreach ($menu as $label => $url)
                        <li class="nav-item">
                            <a @class(['nav-link', 'active' => url()->current() === $url]) href="{{ $url }}"
                               @if (url()->current() === $url) aria-current="page" @endif>{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>

                <ul class="navbar-nav align-items-lg-center gap-lg-2">
                    @guest
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Masuk</a></li>
                        <li class="nav-item"><a class="btn btn-primary btn-sm" href="{{ route('register') }}">Daftar</a></li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1" aria-hidden="true"></i>{{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ auth()->user()->homeUrl() }}">
                                        {{ auth()->user()->isAdmin() ? 'Panel Admin' : 'Dashboard' }}
                                    </a>
                                </li>
                                @if (Route::has('profile.edit'))
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="@yield('main-class', 'py-4 py-lg-5')">
        @if (session()->hasAny(['success', 'status', 'error', 'warning', 'info']))
            <div class="container mt-3">
                <x-alert />
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
            <span><span class="brand-wordmark fs-5">{{ config('app.name') }}</span> Wedding Organizer</span>
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}. Hak cipta dilindungi.</span>
        </div>
    </footer>

    @stack('modals')

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
