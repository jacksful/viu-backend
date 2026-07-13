<?php

namespace App\Jobs;

use App\Mail\CancellationConfirmationMail;
use App\Models\EmailSetting;
use App\Models\UserZipcodeSubscription;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendCancellationConfirmationEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $subscriptionId,
    ) {}

    public function uniqueId(): string
    {
        return 'cancellation-confirmation:'.$this->subscriptionId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $subscription = UserZipcodeSubscription::query()
            ->with('user')
            ->find($this->subscriptionId);

        if (! $subscription || ! $subscription->cancel_at_period_end) {
            return;
        }

        if ($subscription->cancellation_confirmation_sent_at) {
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
        $endDate = $subscription->formattedEndDate();
        $billingPortalUrl = URL::route('user.dashboard');

        try {
            Mail::to($recipient)->send(new CancellationConfirmationMail(
                firstName: $firstName,
                zipCode: $zipCode,
                endDate: $endDate,
                billingPortalUrl: $billingPortalUrl,
            ));

            $subscription->update([
                'cancellation_confirmation_sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send subscription cancellation confirmation email.', [
                'subscription_id' => $subscription->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
