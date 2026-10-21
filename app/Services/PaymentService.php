<?php

namespace App\Services;

use App\Mail\PaymentRejected;
use App\Mail\PaymentVerified;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Pembayaran manual: customer transfer lalu unggah bukti, admin mengecek dan memverifikasi.
 */
class PaymentService
{
    /** Bukti transfer berisi data rekening, jadi disimpan di disk privat dan dibuka lewat route berotorisasi. */
    public const PROOF_DISK = 'local';

    /**
     * @param  array{type: string, amount: int, bank_name: string, account_name: string, proof: UploadedFile}  $data
     */
    public function submit(Booking $booking, array $data): Payment
    {
        return DB::transaction(function () use ($booking, $data) {
            $booking = Booking::with('payments')->lockForUpdate()->findOrFail($booking->id);

            $this->ensureCanPay($booking, $data['type'], (int) $data['amount']);

            return $booking->payments()->create([
                'type' => $data['type'],
                'amount' => $data['amount'],
                'bank_name' => $data['bank_name'],
                'account_name' => $data['account_name'],
                'proof_image' => $data['proof']->store('payments', self::PROOF_DISK),
                'status' => Payment::STATUS_PENDING,
            ]);
        });
    }

    public function verify(Payment $payment, User $admin): Payment
    {
        $payment = DB::transaction(function () use ($payment, $admin) {
            $payment = $this->lockPending($payment);

            $payment->update([
                'status' => Payment::STATUS_VERIFIED,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            $booking = $payment->booking()->with('payments')->lockForUpdate()->first();

            // Lunas otomatis begitu total terverifikasi menutup total harga
            if ($booking->status === Booking::STATUS_CONFIRMED && $booking->verifiedTotal() >= $booking->total_price) {
                $booking->update(['status' => Booking::STATUS_PAID]);
            }

            return $payment;
        });

        Mail::to($payment->booking->user)->send(new PaymentVerified($payment));

        return $payment;
    }

    public function reject(Payment $payment, User $admin, string $reason): Payment
    {
        $payment = DB::transaction(function () use ($payment, $admin, $reason) {
            $payment = $this->lockPending($payment);

            $payment->update([
                'status' => Payment::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            return $payment;
        });

        Mail::to($payment->booking->user)->send(new PaymentRejected($payment));

        return $payment;
    }

    /** Jenis pembayaran yang seharusnya dilakukan berikutnya untuk booking ini. */
    public function nextType(Booking $booking): string
    {
        return $booking->hasVerifiedDp() ? Payment::TYPE_PELUNASAN : Payment::TYPE_DP;
    }

    private function ensureCanPay(Booking $booking, string $type, int $amount): void
    {
        $fail = fn (string $field, string $message) => throw ValidationException::withMessages([$field => $message]);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            $fail('amount', 'Pembayaran hanya bisa dilakukan untuk booking yang sudah dikonfirmasi dan belum lunas.');
        }

        if ($booking->payments->contains('status', Payment::STATUS_PENDING)) {
            $fail('amount', 'Masih ada pembayaran yang menunggu verifikasi. Tunggu hasil pengecekan sebelum mengirim bukti baru.');
        }

        if ($type === Payment::TYPE_DP && $booking->hasVerifiedDp()) {
            $fail('type', 'DP sudah diterima. Silakan lakukan pelunasan.');
        }

        if ($type === Payment::TYPE_PELUNASAN && ! $booking->hasVerifiedDp()) {
            $fail('type', 'Pelunasan bisa dilakukan setelah DP diverifikasi.');
        }

        if ($type === Payment::TYPE_DP && $amount < $booking->minimumDp()) {
            $fail('amount', 'Nominal DP minimal '.rupiah($booking->minimumDp()).' ('.config('restikey.dp_percentage').'% dari total).');
        }

        if ($amount > $booking->remainingAmount()) {
            $fail('amount', 'Nominal melebihi sisa tagihan '.rupiah($booking->remainingAmount()).'.');
        }
    }

    private function lockPending(Payment $payment): Payment
    {
        $payment = Payment::lockForUpdate()->findOrFail($payment->id);

        if ($payment->status !== Payment::STATUS_PENDING) {
            throw ValidationException::withMessages(['payment' => 'Pembayaran ini sudah diproses sebelumnya.']);
        }

        return $payment;
    }
}
