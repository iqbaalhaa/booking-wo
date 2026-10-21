<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /** Daftar semua booking (panel admin). */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $this->owns($user, $booking);
    }

    /** Hanya customer yang membuat booking; admin mengelola, bukan memesan. */
    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

    /** Customer membatalkan booking miliknya; admin bisa menolak/membatalkan booking siapa pun. */
    public function cancel(User $user, Booking $booking): bool
    {
        return ($this->owns($user, $booking) || $user->isAdmin()) && $booking->isCancellable();
    }

    /** Pembayaran (DP / pelunasan) hanya untuk booking yang sudah dikonfirmasi dan masih ada sisa tagihan. */
    public function pay(User $user, Booking $booking): bool
    {
        return $this->owns($user, $booking)
            && $booking->status === Booking::STATUS_CONFIRMED
            && $booking->remainingAmount() > 0;
    }

    public function review(User $user, Booking $booking): bool
    {
        return $this->owns($user, $booking)
            && $booking->status === Booking::STATUS_COMPLETED
            && ! $booking->review()->exists();
    }

    private function owns(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }
}
