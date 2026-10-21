@extends('layouts.app')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<div class="container">
    <x-card class="auth-card" body-class="p-4 p-md-5">
        <h1 class="h2 mb-4">Buat kata sandi baru</h1>

        <form method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}"
                       class="form-control @error('email') is-invalid @enderror" required autocomplete="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata sandi baru</label>
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" autofocus>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="password-confirm" class="form-label">Ulangi kata sandi baru</label>
                <input id="password-confirm" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary w-100">Simpan kata sandi</button>
        </form>
    </x-card>
</div>
@endsection
