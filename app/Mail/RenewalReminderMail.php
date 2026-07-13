<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RenewalReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $zipCode,
        public string $renewalDate,
        public int $daysUntilRenewal,
        public string $amount,
        public string $cardLast4,
        public string $billingPortalUrl,
        public string $unsubscribeUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your ZIP {$this->zipCode} subscription renews soon",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.renewal-reminder',
        );
    }
}
