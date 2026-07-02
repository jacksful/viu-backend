<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeIntakeKickoffMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $zipCode,
        public string $intakeUrl,
        public string $calendarUrl,
        public string $unsubscribeUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Welcome to VIU. ZIP {$this->zipCode} is officially yours",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-intake-kickoff',
        );
    }
}
