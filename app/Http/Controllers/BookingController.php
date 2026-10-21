<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Package;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookings,
        private AvailabilityService $availability,
    ) {
    }

    public function index(Request $request)
    {
        $status = array_key_exists($request->query('status'), Booking::STATUSES) ? $request->query('status') : null;

        $bookings = $request->user()->bookings()
            ->with('package')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bookings.index', compact('bookings', 'status'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Booking::class);

        $packages = Package::activeCatalog();
        $date = $request->query('date');

        return view('bookings.create', [
            'packages' => $packages,
            'addons' => Addon::active()->orderBy('price')->get(),
            'selectedPackage' => $packages->firstWhere('slug', $request->query('package')),
            'selectedDate' => is_string($date) && Carbon::canBeCreatedFromFormat($date, 'Y-m-d') ? $date : null,
            'earliest' => $this->availability->earliestDate(),
            'messages' => collect([
                AvailabilityService::AVAILABLE, AvailabilityService::PENDING, AvailabilityService::BOOKED,
                AvailabilityService::BLOCKED, AvailabilityService::PAST,
            ])->mapWithKeys(fn ($status) => [$status => $this->availability->messageFor($status)]),
        ]);
    }

    public function store(StoreBookingRequest $request)
    {
        $booking = $this->bookings->create($request->user(), $request->validated());

        $redirect = redirect()->route('bookings.show', $booking)->with('success',
            "Booking {$booking->booking_code} berhasil dikirim. Kami akan memeriksa jadwal dan mengonfirmasi booking Anda. "
            .'Setelah dikonfirmasi, bayar DP minimal '.rupiah($booking->minimumDp())
            .' dalam '.config('restikey.auto_cancel_days').' hari agar tanggal tetap milik Anda.');

        if ($this->availability->hasPendingRequest($booking->event_date, $booking->id)) {
            $redirect->with('warning', $this->availability->messageFor(AvailabilityService::PENDING));
        }

        return $redirect;
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['package', 'bookingAddons', 'payments' => fn ($query) => $query->latest(), 'review']);

        return view('bookings.show', compact('booking'));
    }

    public function cancel(CancelBookingRequest $request, Booking $booking)
    {
        $this->bookings->cancel($booking, $request->validated('cancel_reason'));

        return redirect()->route('bookings.show', $booking)->with('success', "Booking {$booking->booking_code} telah dibatalkan.");
    }
}
