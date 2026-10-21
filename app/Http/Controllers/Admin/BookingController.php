<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings)
    {
    }

    public function index(Request $request)
    {
        $status = array_key_exists($request->query('status'), Booking::STATUSES) ? $request->query('status') : null;
        $from = $this->date($request->query('dari'));
        $to = $this->date($request->query('sampai'));
        $q = trim((string) $request->query('q'));

        $bookings = Booking::with(['user', 'package'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($from, fn ($query) => $query->whereDate('event_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('event_date', '<=', $to))
            ->when($q !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('booking_code', 'like', "%{$q}%")
                ->orWhere('groom_name', 'like', "%{$q}%")
                ->orWhere('bride_name', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$q}%"))))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'status' => $status,
            'counts' => Booking::toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'package', 'bookingAddons', 'payments' => fn ($query) => $query->with('verifier')->latest(), 'review']);

        return view('admin.bookings.show', [
            'booking' => $booking,
            // booking pending lain di tanggal yang sama akan dibatalkan otomatis saat dikonfirmasi
            'competing' => Booking::pending()->onDate($booking->event_date)->whereKeyNot($booking->id)->with('user')->get(),
            'dateTaken' => Booking::locking()->onDate($booking->event_date)->whereKeyNot($booking->id)->first(),
        ]);
    }

    public function confirm(Booking $booking)
    {
        $this->bookings->confirm($booking);

        return back()->with('success', "Booking {$booking->booking_code} dikonfirmasi. Email instruksi DP dikirim ke customer.");
    }

    public function cancel(CancelBookingRequest $request, Booking $booking)
    {
        $this->bookings->cancel($booking, $request->validated('cancel_reason'));

        return back()->with('success', "Booking {$booking->booking_code} dibatalkan.");
    }

    public function complete(Booking $booking)
    {
        $this->bookings->complete($booking);

        return back()->with('success', "Booking {$booking->booking_code} ditandai selesai.");
    }

    private function date(?string $value): ?string
    {
        return $value && Carbon::canBeCreatedFromFormat($value, 'Y-m-d') ? $value : null;
    }
}
