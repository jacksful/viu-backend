<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryAcknowledgmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $zipCode,
        public string $amount,
        public string $checkoutUrl,
        public string $unsubscribeUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "ZIP {$this->zipCode} is open. Lock it in.",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-acknowledgment',
        );
    }
}
