<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancellationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $zipCode,
        public string $endDate,
        public string $billingPortalUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your VIU subscription is set to end on {$this->endDate}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cancellation-confirmation',
        );
    }
}
