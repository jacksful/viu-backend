<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionActiveMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $zipCode,
        public string $amount,
        public string $paidDate,
        public string $nextRenewalDate,
        public string $billingIntervalLabel,
        public string $dashboardUrl,
        public string $billingPortalUrl,
        public ?string $receiptPdfUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment received, ZIP {$this->zipCode} is still yours",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-active',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (blank($this->receiptPdfUrl)) {
            return [];
        }

        return [
            Attachment::fromUrl($this->receiptPdfUrl)
                ->as('viu-receipt.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
