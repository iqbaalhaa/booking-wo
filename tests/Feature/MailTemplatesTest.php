<?php

namespace Tests\Feature;

use App\Mail\BookingCancelled;
use App\Mail\BookingConfirmed;
use App\Mail\BookingCreated;
use App\Mail\PaymentRejected;
use App\Mail\PaymentVerified;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_emails_render_in_indonesian_with_key_information(): void
    {
        $booking = Booking::factory()->confirmed()->create();
        $verified = Payment::factory()->verified()->create(['booking_id' => $booking->id]);
        $rejected = Payment::factory()->rejected()->create(['booking_id' => $booking->id]);
        $cancelled = Booking::factory()->cancelled()->create();

        $cases = [
            [new BookingCreated($booking), ['Terima kasih', $booking->booking_code, 'DP minimal']],
            [new BookingCreated($booking, forAdmin: true), ['Booking baru masuk', 'Tinjau booking']],
            [new BookingConfirmed($booking), [config('restikey.bank.account_number'), rupiah($booking->minimumDp()), 'Unggah bukti DP']],
            [new BookingCancelled($cancelled), ['Booking dibatalkan', 'Dibatalkan oleh customer.']],
            [new PaymentVerified($verified), ['Pembayaran diterima', rupiah($verified->amount), 'Sisa tagihan']],
            [new PaymentRejected($rejected), ['Bukti transfer tidak terbaca.', 'Unggah ulang bukti']],
        ];

        foreach ($cases as [$mail, $expected]) {
            $html = $mail->render();
            foreach ($expected as $text) {
                $this->assertStringContainsString(e($text), $html, get_class($mail).' tidak memuat: '.$text);
            }
        }
    }
}
