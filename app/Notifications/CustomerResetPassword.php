<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

class CustomerResetPassword extends ResetPasswordNotification
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(route('user.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset your password')
            ->view('emails.password-reset', [
                'firstName' => $notifiable->first_name,
                'email' => $notifiable->getEmailForPasswordReset(),
                'resetUrl' => $resetUrl,
                'expireMinutes' => Config::get('auth.passwords.users.expire', 60),
            ]);
    }
}
