@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('content')
<div class="container">
    <x-card class="auth-card" body-class="p-4 p-md-5">
        <h1 class="h2 mb-3">Verifikasi email Anda</h1>

        @if (session('resent'))
            <x-alert type="success">Tautan verifikasi baru sudah dikirim ke email Anda.</x-alert>
        @endif

        <p>Sebelum melanjutkan, buka tautan verifikasi yang kami kirim ke email Anda.</p>

        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary">Kirim ulang tautan verifikasi</button>
        </form>
    </x-card>
</div>
@endsection
