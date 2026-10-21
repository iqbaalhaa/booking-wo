@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
    @include('errors._message', [
        'code' => 404,
        'title' => 'Halaman tidak ditemukan',
        'message' => 'Alamat yang Anda buka tidak ada atau sudah dipindahkan. Periksa kembali tautannya, atau mulai dari beranda.',
    ])
@endsection
