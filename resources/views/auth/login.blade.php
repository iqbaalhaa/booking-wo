@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="container">
    <x-card class="auth-card" body-class="p-4 p-md-5">
        <h1 class="h2 mb-1">Masuk</h1>
        <p class="text-body-secondary mb-4">Masuk untuk booking dan memantau pesanan Anda.</p>

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror" required autocomplete="email" autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label">Kata sandi</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small">Lupa kata sandi?</a>
                    @endif
                </div>
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" @checked(old('remember'))>
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Masuk</button>
        </form>

        <p class="text-center small mt-4 mb-0">
            Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
        </p>
    </x-card>
</div>
@endsection
