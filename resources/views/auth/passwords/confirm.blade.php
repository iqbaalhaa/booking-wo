@extends('layouts.app')

@section('title', 'Konfirmasi Kata Sandi')

@section('content')
<div class="container">
    <x-card class="auth-card" body-class="p-4 p-md-5">
        <h1 class="h2 mb-1">Konfirmasi kata sandi</h1>
        <p class="text-body-secondary mb-4">Masukkan kata sandi Anda untuk melanjutkan.</p>

        <form method="POST" action="{{ route('password.confirm') }}" novalidate>
            @csrf

            <div class="mb-4">
                <label for="password" class="form-label">Kata sandi</label>
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password" autofocus>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary w-100">Konfirmasi</button>
        </form>

        @if (Route::has('password.request'))
            <p class="text-center small mt-4 mb-0"><a href="{{ route('password.request') }}">Lupa kata sandi?</a></p>
        @endif
    </x-card>
</div>
@endsection
