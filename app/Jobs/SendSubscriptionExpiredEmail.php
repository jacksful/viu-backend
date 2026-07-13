<?php

namespace App\Jobs;

use App\Mail\SubscriptionExpiredMail;
use App\Models\EmailSetting;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendSubscriptionExpiredEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $subscriptionId,
    ) {}

    public function uniqueId(): string
    {
        return 'subscription-expired:'.$this->subscriptionId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $subscription = UserZipcodeSubscription::query()
            ->with('user')
            ->find($this->subscriptionId);

        if (! $subscription || ! in_array($subscription->status, ['canceled', 'expired'], true)) {
            return;
        }

        if ($subscription->expiration_email_sent_at) {
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
        $endedDate = $subscription->formattedEndDate();
        $amount = $this->formatMonthlyAmount($zipcodes);
        $checkoutUrl = URL::route('home').'#pricing';
        $billingPortalUrl = URL::route('user.dashboard');

        try {
            Mail::to($recipient)->send(new SubscriptionExpiredMail(
                firstName: $firstName,
                zipCode: $zipCode,
                endedDate: $endedDate,
                amount: $amount,
                checkoutUrl: $checkoutUrl,
                billingPortalUrl: $billingPortalUrl,
            ));

            $subscription->update([
                'expiration_email_sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send subscription expired email.', [
                'subscription_id' => $subscription->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function formatMonthlyAmount($zipcodes): string
    {
        $total = $zipcodes->sum(function (Zipcode $zipcode): float {
            return (float) ($zipcode->monthly_price ?? 0);
        });

        return '$'.number_format($total, 2);
    }
}
