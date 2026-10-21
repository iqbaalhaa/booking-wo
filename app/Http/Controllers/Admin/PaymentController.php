<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments)
    {
    }

    public function index(Request $request)
    {
        // Default: yang perlu dicek; ?status=semua untuk semua
        $status = $request->query('status', Payment::STATUS_PENDING);
        $status = array_key_exists($status, Payment::STATUSES) ? $status : null;

        $payments = Payment::with(['booking.user', 'verifier'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy($status === Payment::STATUS_PENDING ? 'created_at' : 'updated_at', $status === Payment::STATUS_PENDING ? 'asc' : 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'status'));
    }

    public function verify(Request $request, Payment $payment)
    {
        $this->payments->verify($payment, $request->user());

        $booking = $payment->booking->refresh();

        return back()->with('success', 'Pembayaran '.rupiah($payment->amount)." untuk {$booking->booking_code} diverifikasi."
            .($booking->status === Booking::STATUS_PAID ? ' Booking sekarang lunas.' : ''));
    }

    public function reject(RejectPaymentRequest $request, Payment $payment)
    {
        $this->payments->reject($payment, $request->user(), $request->validated('rejection_reason'));

        return back()->with('success', 'Pembayaran ditolak. Customer diminta mengunggah ulang bukti.');
    }
}
