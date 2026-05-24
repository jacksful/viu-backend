<?php

namespace App\Observers;

use App\Models\Contact;
use App\Models\EmailSetting;
use App\Notifications\NewInterestedPersonNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ContactObserver
{
    public function created(Contact $contact): void
    {
        try {
            EmailSetting::applyMailConfig();

            $settings = EmailSetting::singleton();

            if (! $settings->admin_notification_enabled) {
                return;
            }

            $adminEmail = $settings->admin_notification_address ?: config('mail.admin_address');

            if (blank($adminEmail)) {
                return;
            }

            Notification::route('mail', $adminEmail)
                ->notify(new NewInterestedPersonNotification($contact));
        } catch (Throwable $e) {
            Log::error('Failed to send admin notification for new contact.', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
