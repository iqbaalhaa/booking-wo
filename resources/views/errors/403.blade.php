@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
    @include('errors._message', [
        'code' => 403,
        'title' => 'Anda tidak punya akses ke halaman ini',
        // Pesan dari abort() (mis. "Halaman ini khusus admin.") ditampilkan bila ada
        'message' => $exception->getMessage() && $exception->getMessage() !== 'This action is unauthorized.'
            ? $exception->getMessage()
            : 'Halaman ini hanya bisa dibuka oleh pemilik data atau admin. Pastikan Anda masuk dengan akun yang benar.',
    ])
@endsection
