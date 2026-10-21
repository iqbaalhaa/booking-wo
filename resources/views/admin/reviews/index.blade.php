@extends('layouts.admin')

@section('title', 'Ulasan')

@section('content')
    <nav class="nav nav-pills gap-2 mb-3" aria-label="Filter ulasan">
        @foreach ([null => 'Semua', 'tidak' => 'Belum tampil', 'ya' => 'Tampil di website'] as $key => $label)
            <a @class(['nav-link', 'active' => $filter === ($key ?: null)])
               href="{{ route('admin.reviews.index', $key ? ['tampil' => $key] : []) }}">{{ $label }}</a>
        @endforeach
    </nav>

    <p class="small text-body-secondary">
        Ulasan baru tidak langsung tampil. Ulasan yang ditampilkan muncul di halaman beranda dan halaman detail paket.
    </p>

    <x-card body-class="p-0">
        @forelse ($reviews as $review)
            <div class="review-row">
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="rating-stars" aria-label="Rating {{ $review->rating }} dari 5">
                            @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}" aria-hidden="true"></i>@endfor
                        </span>
                        <span class="fw-medium">{{ $review->user->name }}</span>
                        <span class="small text-body-secondary">
                            Paket {{ $review->booking->package->name }},
                            <a href="{{ route('admin.bookings.show', $review->booking) }}">{{ $review->booking->booking_code }}</a>,
                            {{ tanggal_indo($review->created_at) }}
                        </span>
                    </div>
                    <p class="mb-0">{{ $review->comment }}</p>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    <form method="POST" action="{{ route('admin.reviews.toggle', $review) }}">
                        @csrf @method('PATCH')
                        <button type="submit" @class(['btn btn-sm text-nowrap', 'btn-outline-secondary' => $review->is_published, 'btn-primary' => ! $review->is_published])>
                            {{ $review->is_published ? 'Sembunyikan' : 'Tampilkan' }}
                        </button>
                    </form>
                    <x-confirm-form :action="route('admin.reviews.destroy', $review)" method="DELETE"
                                    title="Hapus ulasan?" message="Ulasan dihapus permanen. Customer bisa mengirim ulasan baru untuk booking ini."
                                    confirm-label="Hapus">
                        Hapus
                    </x-confirm-form>
                </div>
            </div>
        @empty
            <p class="text-body-secondary text-center p-4 mb-0">Belum ada ulasan.</p>
        @endforelse

        @if ($reviews->hasPages())
            <x-slot:footer>{{ $reviews->links() }}</x-slot:footer>
        @endif
    </x-card>
@endsection
