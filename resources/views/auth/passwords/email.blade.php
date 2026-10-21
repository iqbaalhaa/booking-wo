@extends('layouts.app')

@section('title', 'Lupa Kata Sandi')

@section('content')
<div class="container">
    <x-card class="auth-card" body-class="p-4 p-md-5">
        <h1 class="h2 mb-1">Lupa kata sandi</h1>
        <p class="text-body-secondary mb-4">Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.</p>

        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror" required autocomplete="email" autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary w-100">Kirim tautan atur ulang</button>
        </form>

        <p class="text-center small mt-4 mb-0"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
    </x-card>
</div>
@endsection
