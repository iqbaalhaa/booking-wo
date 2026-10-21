<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public bool $forAdmin = false)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->forAdmin
            ? "Booking baru {$this->booking->booking_code} menunggu konfirmasi"
            : "Booking {$this->booking->booking_code} sudah kami terima");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.bookings.created');
    }
}
