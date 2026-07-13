<?php

namespace App\Jobs;

use App\Mail\IntakeReminderMail;
use App\Models\EmailSetting;
use App\Models\UserZipcodeSubscription;
use App\Support\IntakeUrl;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendIntakeReminderEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $subscriptionId,
    ) {}

    public function uniqueId(): string
    {
        return 'intake-reminder:'.$this->subscriptionId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $subscription = UserZipcodeSubscription::query()
            ->with(['user', 'customerIntake'])
            ->find($this->subscriptionId);

        if (! $subscription || $subscription->status !== 'active') {
            return;
        }

        if ($subscription->intake_reminder_sent_at !== null) {
            return;
        }

        if ($subscription->customerIntake?->isSubmitted()) {
            return;
        }

        $user = $subscription->user;

        if (! $user || $user->role !== 'customer') {
            return;
        }

        $recipient = $user->email;

        if (blank($recipient)) {
            return;
        }

        $zipcodes = $subscription->zipcodes;

        if ($zipcodes->isEmpty()) {
            return;
        }

        $firstName = filled($user->first_name) ? $user->first_name : 'there';
        $zipCode = (string) $zipcodes->first()->code;
        $intakeUrl = IntakeUrl::forSubscription($subscription);
        $unsubscribeUrl = config('viu.unsubscribe_url') ?: 'mailto:'.(config('mail.from.address') ?: 'support@fullviu.com');

        try {
            Mail::to($recipient)->send(new IntakeReminderMail(
                firstName: $firstName,
                zipCode: $zipCode,
                intakeUrl: $intakeUrl,
                unsubscribeUrl: $unsubscribeUrl,
            ));

            $subscription->update([
                'intake_reminder_sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send intake reminder email.', [
                'subscription_id' => $subscription->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
