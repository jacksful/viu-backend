<?php

namespace App\Jobs;

use App\Mail\RenewalReminderMail;
use App\Models\EmailSetting;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use App\Services\StripeService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendRenewalReminderEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $subscriptionId,
    ) {}

    public function uniqueId(): string
    {
        return 'renewal-reminder:'.$this->subscriptionId;
    }

    public function handle(StripeService $stripe): void
    {
        EmailSetting::applyMailConfig();

        $subscription = UserZipcodeSubscription::query()
            ->with('user')
            ->find($this->subscriptionId);

        if (! $subscription || $subscription->status !== 'active') {
            return;
        }

        if ($subscription->cancel_at_period_end || ! $subscription->end_date) {
            return;
        }

        if ($subscription->renewal_reminder_sent_for_end_date?->toDateString() === $subscription->end_date->toDateString()) {
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
        $renewalDate = $subscription->formattedEndDate();
        $daysUntilRenewal = max(0, (int) now()->startOfDay()->diffInDays($subscription->end_date->startOfDay(), false));
        $amount = $this->formatSubscriptionAmount($subscription, $zipcodes);
        $cardLast4 = $this->resolveCardLast4($stripe, $subscription);
        $billingPortalUrl = URL::route('user.dashboard');
        $unsubscribeUrl = config('viu.unsubscribe_url') ?: 'mailto:'.(config('mail.from.address') ?: 'support@fullviu.com');

        try {
            Mail::to($recipient)->send(new RenewalReminderMail(
                firstName: $firstName,
                zipCode: $zipCode,
                renewalDate: $renewalDate,
                daysUntilRenewal: $daysUntilRenewal,
                amount: $amount,
                cardLast4: $cardLast4,
                billingPortalUrl: $billingPortalUrl,
                unsubscribeUrl: $unsubscribeUrl,
            ));

            $subscription->update([
                'renewal_reminder_sent_for_end_date' => $subscription->end_date,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send subscription renewal reminder email.', [
                'subscription_id' => $subscription->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function formatSubscriptionAmount(UserZipcodeSubscription $subscription, $zipcodes): string
    {
        $interval = $subscription->billing_interval ?: Zipcode::BILLING_MONTHLY;
        $total = $zipcodes->sum(function (Zipcode $zipcode) use ($interval): float {
            if ($interval === Zipcode::BILLING_YEARLY) {
                return (float) ($zipcode->yearly_price ?? 0);
            }

            return (float) ($zipcode->monthly_price ?? 0);
        });

        return '$'.number_format($total, 2);
    }

    protected function resolveCardLast4(StripeService $stripe, UserZipcodeSubscription $subscription): string
    {
        if (! $stripe->isEnabled() || blank($subscription->stripe_customer_id)) {
            return '----';
        }

        try {
            $customer = $stripe->client()->customers->retrieve(
                $subscription->stripe_customer_id,
                ['expand' => ['invoice_settings.default_payment_method']],
            );

            $paymentMethod = $customer->invoice_settings->default_payment_method ?? null;

            if (is_string($paymentMethod) && filled($paymentMethod)) {
                $paymentMethod = $stripe->client()->paymentMethods->retrieve($paymentMethod);
            }

            if (is_object($paymentMethod) && isset($paymentMethod->card->last4)) {
                return (string) $paymentMethod->card->last4;
            }
        } catch (Throwable) {
            //
        }

        return '----';
    }
}
