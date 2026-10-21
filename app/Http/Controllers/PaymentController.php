<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments)
    {
    }

    public function create(Booking $booking)
    {
        $this->authorize('pay', $booking);

        $booking->load(['package', 'payments' => fn ($query) => $query->latest()]);

        if ($booking->payments->contains('status', Payment::STATUS_PENDING)) {
            return redirect()->route('bookings.show', $booking)
                ->with('info', 'Bukti pembayaran Anda sedang dicek. Tunggu hasil verifikasi sebelum mengirim bukti baru.');
        }

        $type = $this->payments->nextType($booking);

        return view('payments.create', [
            'booking' => $booking,
            'type' => $type,
            // DP: isian awal = DP minimal; pelunasan: isian awal = sisa tagihan
            'suggestedAmount' => $type === Payment::TYPE_DP ? $booking->minimumDp() : $booking->remainingAmount(),
            'pastDeadline' => $type === Payment::TYPE_PELUNASAN && today()->gt($booking->paymentDeadline()),
        ]);
    }

    public function store(StorePaymentRequest $request, Booking $booking)
    {
        $payment = $this->payments->submit($booking, [
            ...$request->safe()->except('proof'),
            'proof' => $request->file('proof'),
        ]);

        return redirect()->route('bookings.show', $booking)->with('success',
            "Bukti pembayaran {$payment->typeLabel()} sebesar ".rupiah($payment->amount).' terkirim. Tim kami akan mengeceknya dan mengabari Anda lewat email.');
    }

    /** Buka bukti transfer (disk privat) hanya untuk pemilik booking atau admin. */
    public function proof(Payment $payment)
    {
        $this->authorize('view', $payment->booking);

        if (Str::startsWith($payment->proof_image, 'http')) {
            return redirect()->away($payment->proof_image); // data demo seeder
        }

        abort_unless(Storage::disk(PaymentService::PROOF_DISK)->exists($payment->proof_image), 404);

        return Storage::disk(PaymentService::PROOF_DISK)->response($payment->proof_image, null, [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
