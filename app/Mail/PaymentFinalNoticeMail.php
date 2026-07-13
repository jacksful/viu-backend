<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentFinalNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $zipCode,
        public string $amount,
        public string $deadlineDate,
        public string $billingPortalUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Final notice, ZIP {$this->zipCode} will be released",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-final-notice',
        );
    }
}
