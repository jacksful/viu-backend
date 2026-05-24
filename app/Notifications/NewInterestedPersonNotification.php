<?php

namespace App\Notifications;

use App\Filament\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInterestedPersonNotification extends Notification
{

    public function __construct(public Contact $contact) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $contact = $this->contact;

        $mail = (new MailMessage)
            ->subject('New Interested Person: '.$contact->name)
            ->greeting('New submission received')
            ->line('Someone has expressed interest through the website form.')
            ->line('**Name:** '.$contact->name)
            ->line('**Email:** '.$contact->email);

        if ($contact->phone) {
            $mail->line('**Phone:** '.$contact->phone);
        }

        if ($contact->zip_of_interest) {
            $mail->line('**ZIP of interest:** '.$contact->zip_of_interest);
        }

        if ($contact->message) {
            $mail->line('**Message:**')
                ->line($contact->message);
        }

        return $mail
            ->action('View in Admin', ContactResource::getUrl('index', panel: 'admin'))
            ->salutation('Regards, '.config('app.name'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'contact_id' => $this->contact->id,
        ];
    }
}
