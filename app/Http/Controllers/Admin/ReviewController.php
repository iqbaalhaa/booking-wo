<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $filter = in_array($request->query('tampil'), ['ya', 'tidak'], true) ? $request->query('tampil') : null;

        $reviews = Review::with(['user', 'booking.package'])
            ->when($filter, fn ($query) => $query->where('is_published', $filter === 'ya'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews', 'filter'));
    }

    public function toggle(Review $review)
    {
        $review->update(['is_published' => ! $review->is_published]);

        return back()->with('success', $review->is_published
            ? "Ulasan dari {$review->user->name} sekarang tampil di website."
            : "Ulasan dari {$review->user->name} disembunyikan dari website.");
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Ulasan dihapus. Customer bisa mengirim ulasan baru untuk booking tersebut.');
    }
}
