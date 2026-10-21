<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentVerified extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Pembayaran {$this->payment->typeLabel()} booking {$this->payment->booking->booking_code} diterima");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.payments.verified', with: ['booking' => $this->payment->booking->load('payments')]);
    }
}
