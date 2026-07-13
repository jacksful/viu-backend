<?php

namespace App\Jobs;

use App\Mail\PaymentFinalNoticeMail;
use App\Models\EmailSetting;
use App\Models\StripePayment;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendPaymentFinalNoticeEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $subscriptionId,
    ) {}

    public function uniqueId(): string
    {
        return 'payment-final-notice:'.$this->subscriptionId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $subscription = UserZipcodeSubscription::query()
            ->with('user')
            ->find($this->subscriptionId);

        if (! $subscription || ! in_array($subscription->status, ['active', 'expired'], true)) {
            return;
        }

        if ($subscription->cancel_at_period_end || ! $subscription->end_date) {
            return;
        }

        if ($subscription->final_notice_sent_for_end_date?->toDateString() === $subscription->end_date->toDateString()) {
            return;
        }

        if (! $this->isAtPaymentRisk($subscription)) {
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
        $deadlineDate = $subscription->formattedEndDate();
        $amount = $this->formatSubscriptionAmount($subscription, $zipcodes);
        $billingPortalUrl = URL::route('user.dashboard');

        try {
            Mail::to($recipient)->send(new PaymentFinalNoticeMail(
                firstName: $firstName,
                zipCode: $zipCode,
                amount: $amount,
                deadlineDate: $deadlineDate,
                billingPortalUrl: $billingPortalUrl,
            ));

            $subscription->update([
                'final_notice_sent_for_end_date' => $subscription->end_date,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send subscription payment final notice email.', [
                'subscription_id' => $subscription->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function isAtPaymentRisk(UserZipcodeSubscription $subscription): bool
    {
        if ($subscription->status === 'expired') {
            return true;
        }

        $latestPayment = StripePayment::query()
            ->where('user_zipcode_subscription_id', $subscription->id)
            ->orderByDesc('id')
            ->first();

        return $latestPayment?->status === 'failed';
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
}
