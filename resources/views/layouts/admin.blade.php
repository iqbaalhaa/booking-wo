<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">

    <title>@yield('title', 'Dashboard') | Admin {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,500i|jost:400,500" rel="stylesheet">

    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="admin-body">
    @php
        // [route, ikon, label, pola route aktif]. Item yang route-nya belum dibuat tampil nonaktif.
        $menu = [
            ['admin.dashboard', 'speedometer2', 'Dashboard', 'admin.dashboard'],
            ['admin.bookings.index', 'calendar-heart', 'Booking', 'admin.bookings.*'],
            ['admin.payments.index', 'cash-coin', 'Pembayaran', 'admin.payments.*'],
            ['admin.packages.index', 'box2-heart', 'Paket', 'admin.packages.*'],
            ['admin.addons.index', 'plus-square', 'Add-on', 'admin.addons.*'],
            ['admin.portfolios.index', 'images', 'Portofolio', 'admin.portfolios.*'],
            ['admin.blocked-dates.index', 'calendar-x', 'Tanggal Diblokir', 'admin.blocked-dates.*'],
            ['admin.reviews.index', 'star', 'Ulasan', 'admin.reviews.*'],
            ['admin.reports.bookings', 'file-earmark-bar-graph', 'Laporan', 'admin.reports.*'],
        ];
    @endphp

    <div class="d-flex">
        {{-- Sidebar: statis di layar lebar, offcanvas yang bisa dibuka/tutup di layar kecil --}}
        <div class="admin-sidebar-wrap">
            {{-- offcanvas-lg menyembunyikan header-nya di layar lebar, jadi brand desktop terpisah --}}
            <a href="{{ route('admin.dashboard') }}" class="brand-wordmark admin-brand d-none d-lg-block">{{ config('app.name') }}</a>
            <aside class="offcanvas-lg offcanvas-start admin-sidebar" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
                <div class="offcanvas-header">
                    <a href="{{ route('admin.dashboard') }}" class="brand-wordmark text-decoration-none" id="adminSidebarLabel">{{ config('app.name') }}</a>
                    <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Tutup menu"></button>
                </div>
                <div class="offcanvas-body">
                    <nav class="nav flex-column w-100" aria-label="Menu admin">
                        @foreach ($menu as [$route, $icon, $label, $pattern])
                            @if (Route::has($route))
                                <a @class(['nav-link', 'active' => request()->routeIs($pattern)]) href="{{ route($route) }}"
                                   @if (request()->routeIs($pattern)) aria-current="page" @endif>
                                    <i class="bi bi-{{ $icon }}" aria-hidden="true"></i> {{ $label }}
                                </a>
                            @else
                                <span class="nav-link disabled" aria-disabled="true">
                                    <i class="bi bi-{{ $icon }}" aria-hidden="true"></i> {{ $label }}
                                </span>
                            @endif
                        @endforeach
                    </nav>
                </div>
            </aside>
        </div>

        <div class="admin-main flex-grow-1">
            <header class="admin-topbar">
                <button class="btn btn-link text-body d-lg-none p-0 me-2" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Buka menu">
                    <i class="bi bi-list fs-3" aria-hidden="true"></i>
                </button>

                <h1 class="h4 mb-0 me-auto">@yield('title', 'Dashboard')</h1>

                <div class="dropdown">
                    <button class="btn btn-link text-body text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-body-secondary">{{ auth()->user()->email }}</span></li>
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil & kata sandi</a></li>
                        <li><a class="dropdown-item" href="{{ route('landing') }}" target="_blank">Lihat situs</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Keluar</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </header>

            <main class="p-3 p-lg-4">
                <x-alert />
                {{-- Error aturan bisnis dari service (bukan milik field form tertentu) --}}
                @foreach (['booking', 'payment', 'cancel_reason', 'rejection_reason'] as $field)
                    @error($field)<x-alert type="error">{{ $message }}</x-alert>@enderror
                @endforeach
                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
