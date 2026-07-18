<?php

namespace App\Notifications;

use App\Filament\Resources\WaitlistResource;
use App\Models\Waitlist;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewWaitlistEntryNotification extends Notification
{
    public function __construct(public Waitlist $waitlist) {}

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
            ->subject('New waitlist entry: '.$this->waitlist->name.' (ZIP '.$this->waitlist->zip_code.')')
            ->view('emails.admin-new-waitlist-entry', [
                'waitlist' => $this->waitlist,
                'adminUrl' => WaitlistResource::getUrl('index', panel: 'admin'),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'waitlist_id' => $this->waitlist->id,
        ];
    }
}
