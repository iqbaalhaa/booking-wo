<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use Illuminate\Database\UniqueConstraintViolationException;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Booking $booking)
    {
        try {
            $booking->review()->create([
                ...$request->validated(),
                'user_id' => $request->user()->id,
                'is_published' => false, // tampil setelah dimoderasi admin
            ]);
        } catch (UniqueConstraintViolationException) {
            // Kiriman ganda (dua tab / klik dua kali) ditahan unique index booking_id
            return redirect()->route('bookings.show', $booking)->with('info', 'Ulasan untuk booking ini sudah tersimpan sebelumnya.');
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Terima kasih atas ulasan Anda! Ulasan akan tampil setelah ditinjau tim kami.');
    }
}
