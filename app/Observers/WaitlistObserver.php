<?php

namespace App\Observers;

use App\Filament\Resources\WaitlistResource;
use App\Jobs\SendWaitlistConfirmationEmail;
use App\Models\EmailSetting;
use App\Models\Waitlist;
use App\Notifications\NewWaitlistEntryNotification;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class WaitlistObserver
{
    public function created(Waitlist $waitlist): void
    {
        $this->notifyAdmin($waitlist);
        $this->sendWaitlistConfirmation($waitlist);
    }

    private function notifyAdmin(Waitlist $waitlist): void
    {
        AdminNotificationService::notifyAll(
            type: 'waitlist_entry',
            title: 'New waitlist entry',
            description: "{$waitlist->name} joined the waitlist for ZIP {$waitlist->zip_code}.",
            data: [
                'waitlist_id' => $waitlist->id,
                'url' => WaitlistResource::getUrl('index', panel: 'admin'),
            ],
        );

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
                ->notify(new NewWaitlistEntryNotification($waitlist));
        } catch (Throwable $e) {
            Log::error('Failed to send admin notification for new waitlist entry.', [
                'waitlist_id' => $waitlist->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendWaitlistConfirmation(Waitlist $waitlist): void
    {
        if (blank($waitlist->zip_code) || blank($waitlist->email)) {
            return;
        }

        SendWaitlistConfirmationEmail::dispatch($waitlist->id);
    }
}
