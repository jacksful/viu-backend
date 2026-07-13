<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $zipCode,
        public string $endedDate,
        public string $amount,
        public string $checkoutUrl,
        public string $billingPortalUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your subscription has ended, ZIP {$this->zipCode} is open again",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-expired',
        );
    }
}
