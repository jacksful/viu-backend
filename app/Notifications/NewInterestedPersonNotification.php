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

        return (new MailMessage)
            ->subject('New Interested Person: '.$contact->name)
            ->view('emails.admin-new-interested-person', [
                'contact' => $contact,
                'adminUrl' => ContactResource::getUrl('index', panel: 'admin'),
            ]);
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
