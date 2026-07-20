<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SensitiveParameter;

class AdminLoginOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        #[SensitiveParameter]
        public string $code,
        public int $codeExpiryMinutes,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your admin login verification code')
            ->view('emails.admin-login-otp', [
                'firstName' => $notifiable->first_name ?? 'Admin',
                'code' => $this->code,
                'codeExpiryMinutes' => $this->codeExpiryMinutes,
            ]);
    }
}
