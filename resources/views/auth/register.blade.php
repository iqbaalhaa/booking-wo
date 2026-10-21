@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
<div class="container">
    <x-card class="auth-card" body-class="p-4 p-md-5">
        <h1 class="h2 mb-1">Daftar akun</h1>
        <p class="text-body-secondary mb-4">Dengan akun, Anda bisa booking tanggal dan memantau pembayaran.</p>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Nama lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                       class="form-control @error('name') is-invalid @enderror" required autocomplete="name" autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror" required autocomplete="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Nomor telepon / WhatsApp</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="081234567890"
                       class="form-control @error('phone') is-invalid @enderror" required autocomplete="tel" inputmode="tel">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata sandi</label>
                <input id="password" type="password" name="password" aria-describedby="password-help"
                       class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                <div id="password-help" class="form-text">Minimal 8 karakter.</div>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="password-confirm" class="form-label">Ulangi kata sandi</label>
                <input id="password-confirm" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary w-100">Daftar</button>
        </form>

        <p class="text-center small mt-4 mb-0">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
        </p>
    </x-card>
</div>
@endsection
