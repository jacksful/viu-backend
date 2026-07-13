<?php

namespace App\Jobs;

use App\Mail\CardExpiringMail;
use App\Models\EmailSetting;
use App\Models\UserZipcodeSubscription;
use App\Services\StripeService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendCardExpiringEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $subscriptionId,
        public int $daysBeforeExpiry = 30,
    ) {}

    public function uniqueId(): string
    {
        return 'card-expiring:'.$this->subscriptionId;
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

        if ($subscription->cancel_at_period_end || blank($subscription->stripe_customer_id)) {
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

        $card = $this->resolveCardDetails($stripe, $subscription);

        if ($card === null) {
            return;
        }

        $cardExpKey = sprintf('%02d/%02d', $card['exp_month'], $card['exp_year'] % 100);

        if ($subscription->card_expiring_notice_sent_for_exp === $cardExpKey) {
            return;
        }

        if (! $this->isCardExpiringSoon($card, $subscription)) {
            return;
        }

        $firstName = filled($user->first_name) ? $user->first_name : 'there';
        $zipCode = (string) $zipcodes->first()->code;
        $renewalDate = $subscription->formattedEndDate();
        $billingPortalUrl = URL::route('user.dashboard');

        try {
            Mail::to($recipient)->send(new CardExpiringMail(
                firstName: $firstName,
                zipCode: $zipCode,
                cardLast4: $card['last4'],
                cardExpMonthYear: $cardExpKey,
                renewalDate: $renewalDate,
                billingPortalUrl: $billingPortalUrl,
            ));

            $subscription->update([
                'card_expiring_notice_sent_for_exp' => $cardExpKey,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send card expiring email.', [
                'subscription_id' => $subscription->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * @return array{last4: string, exp_month: int, exp_year: int}|null
     */
    protected function resolveCardDetails(StripeService $stripe, UserZipcodeSubscription $subscription): ?array
    {
        if (! $stripe->isEnabled()) {
            return null;
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

            if (
                is_object($paymentMethod)
                && isset($paymentMethod->card->last4, $paymentMethod->card->exp_month, $paymentMethod->card->exp_year)
            ) {
                return [
                    'last4' => (string) $paymentMethod->card->last4,
                    'exp_month' => (int) $paymentMethod->card->exp_month,
                    'exp_year' => (int) $paymentMethod->card->exp_year,
                ];
            }
        } catch (Throwable) {
            //
        }

        return null;
    }

    /**
     * @param  array{last4: string, exp_month: int, exp_year: int}  $card
     */
    protected function isCardExpiringSoon(array $card, UserZipcodeSubscription $subscription): bool
    {
        $cardExpiresAt = Carbon::create($card['exp_year'], $card['exp_month'], 1)->endOfMonth()->startOfDay();
        $noticeWindowEnd = now()->startOfDay()->addDays(max(1, $this->daysBeforeExpiry));

        if ($cardExpiresAt->greaterThan($noticeWindowEnd)) {
            return false;
        }

        if ($subscription->end_date) {
            return $cardExpiresAt->lessThanOrEqualTo($subscription->end_date->startOfDay());
        }

        return true;
    }
}
