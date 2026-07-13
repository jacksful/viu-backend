<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CardExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $zipCode,
        public string $cardLast4,
        public string $cardExpMonthYear,
        public string $renewalDate,
        public string $billingPortalUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your card on file is about to expire',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.card-expiring',
        );
    }
}
