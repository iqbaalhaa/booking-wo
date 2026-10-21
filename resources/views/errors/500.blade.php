{{--
    Berdiri sendiri (tidak memakai layouts.app): error 500 bisa terjadi saat database/sesi bermasalah,
    sedangkan layout membaca user yang login dan bisa ikut gagal.
--}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Terjadi Kesalahan | {{ config('app.name') }}</title>
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600|jost:400,500" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <nav class="navbar bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand brand-wordmark" href="{{ url('/') }}">{{ config('app.name') }}</a>
        </div>
    </nav>

    <main class="container">
        <div class="error-page">
            <div class="error-code" aria-hidden="true">500</div>
            <h1 class="mb-3">Maaf, terjadi kesalahan di sistem kami</h1>
            <p class="section-lead mx-auto mb-4">
                Kesalahan ini sudah dicatat oleh sistem. Silakan muat ulang halaman beberapa saat lagi.
                Jika Anda sedang mengirim booking atau pembayaran, cek dulu di halaman Booking Saya sebelum mengirim ulang.
            </p>
            <a href="{{ url('/') }}" class="btn btn-primary">Ke beranda</a>
        </div>
    </main>
</body>
</html>
