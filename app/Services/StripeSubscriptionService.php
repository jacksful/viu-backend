<?php

namespace App\Services;

use App\Jobs\SendWelcomeIntakeKickoffEmail;
use App\Models\Lead;
use App\Models\StripePayment;
use App\Models\User;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Invoice;
use Stripe\Subscription;

class StripeSubscriptionService
{
    public function __construct(
        protected StripeService $stripe,
    ) {}

    /**
     * @param  array{name: string, email: string, phone?: string|null, company?: string|null, billing_interval?: string|null}  $customer
     */
    public function createCheckoutSession(Zipcode $zipcode, array $customer): CheckoutSession
    {
        if (! $this->stripe->isEnabled()) {
            throw new \RuntimeException('Stripe payments are not enabled.');
        }

        $this->assertZipcodeAvailable($zipcode);

        $billingInterval = $customer['billing_interval'] ?? Zipcode::BILLING_MONTHLY;

        try {
            $plan = $zipcode->resolveBillingPlan($billingInterval);
        } catch (\InvalidArgumentException $exception) {
            throw new \RuntimeException($exception->getMessage());
        }

        $settings = $this->stripe->settings();
        $amountCents = $plan['amount_cents'];
        $stripePriceId = $zipcode->assertStripePriceForInterval($plan['interval']);

        $lineItem = [
            'price' => $stripePriceId,
            'quantity' => 1,
        ];

        $lead = Lead::create([
            'name' => $customer['name'],
            'email' => $customer['email'],
            'phone' => $customer['phone'] ?? null,
            'initial_notes' => $customer['company'] ?? null,
            'lead_status' => 'interested',
            'payment_status' => 'unpaid',
        ]);

        $lead->zipcodes()->attach($zipcode->id);

        $session = $this->stripe->client()->checkout->sessions->create([
            'mode' => 'subscription',
            'customer_email' => $customer['email'],
            'line_items' => [$lineItem],
            'metadata' => [
                'lead_id' => (string) $lead->id,
                'zipcode_id' => (string) $zipcode->id,
                'billing_interval' => $plan['interval'],
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'] ?? '',
            ],
            'subscription_data' => [
                'metadata' => [
                    'lead_id' => (string) $lead->id,
                    'zipcode_id' => (string) $zipcode->id,
                    'billing_interval' => $plan['interval'],
                ],
            ],
            'success_url' => $settings->resolvedSuccessUrl().'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $settings->resolvedCancelUrl(),
        ]);

        $lead->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        StripePayment::create([
            'lead_id' => $lead->id,
            'zipcode_id' => $zipcode->id,
            'stripe_checkout_session_id' => $session->id,
            'amount_cents' => $amountCents,
            'currency' => $settings->currency ?: 'usd',
            'status' => 'checkout_pending',
            'billing_interval' => $plan['interval'],
            'customer_email' => $customer['email'],
            'customer_name' => $customer['name'],
            'metadata' => [
                'zipcode_code' => $zipcode->code,
                'billing_interval' => $plan['interval'],
                'billing_label' => $plan['label'],
                'stripe_price_id' => $stripePriceId,
            ],
        ]);

        return $session;
    }

    public function fulfillCheckoutSession(CheckoutSession $session): void
    {
        if ($session->mode !== 'subscription' || $session->payment_status !== 'paid') {
            return;
        }

        $metadata = $session->metadata?->toArray() ?? [];
        $leadId = $metadata['lead_id'] ?? null;
        $zipcodeId = $metadata['zipcode_id'] ?? null;

        if (! $leadId || ! $zipcodeId) {
            return;
        }

        $lead = Lead::query()->find($leadId);
        $zipcode = Zipcode::query()->find($zipcodeId);

        if (! $lead || ! $zipcode) {
            return;
        }

        if ($lead->payment_status === 'paid' && $lead->converted_to_user_id) {
            $subscriptionId = is_string($session->subscription) ? $session->subscription : $session->subscription?->id;
            $stripeSubscription = $this->resolveStripeSubscription($session, $subscriptionId);

            if ($stripeSubscription && $subscriptionId) {
                $localSubscription = UserZipcodeSubscription::query()
                    ->where('stripe_subscription_id', $subscriptionId)
                    ->first();

                if ($localSubscription) {
                    $this->syncLocalSubscriptionPeriod($localSubscription, $stripeSubscription);
                }
            }

            return;
        }

        $subscriptionId = is_string($session->subscription) ? $session->subscription : $session->subscription?->id;
        $customerId = is_string($session->customer) ? $session->customer : $session->customer?->id;
        $billingInterval = $metadata['billing_interval']
            ?? ($subscriptionId ? $this->resolveBillingIntervalFromStripe($subscriptionId) : Zipcode::BILLING_MONTHLY);
        $stripeSubscription = $this->resolveStripeSubscription($session, $subscriptionId);

        $fulfilledPaymentId = null;

        DB::transaction(function () use ($lead, $zipcode, $session, $subscriptionId, $customerId, $billingInterval, $stripeSubscription, &$fulfilledPaymentId): void {
            if (! $this->isZipcodeAvailable($zipcode)) {
                if ($subscriptionId) {
                    $this->stripe->client()->subscriptions->cancel($subscriptionId);
                }

                $lead->update([
                    'payment_status' => 'unpaid',
                    'internal_comments' => trim(($lead->internal_comments ?? '')."\nStripe subscription cancelled: ZIP no longer available."),
                ]);

                StripePayment::query()
                    ->where('stripe_checkout_session_id', $session->id)
                    ->update(['status' => 'cancelled_unavailable']);

                return;
            }

            $user = $this->resolveCustomerUser($lead, $customerId, $session->customer_email ?? $lead->email);
            $subscription = $this->activateTerritorySubscription(
                $user,
                $zipcode,
                $subscriptionId,
                $customerId,
                $billingInterval,
                $stripeSubscription,
            );

            $lead->update([
                'payment_status' => 'paid',
                'lead_status' => 'interested',
                'stripe_checkout_session_id' => $session->id,
                'stripe_subscription_id' => $subscriptionId,
                'converted_to_user_id' => $user->id,
                'converted_at' => $lead->converted_at ?? now(),
            ]);

            StripePayment::query()
                ->where('stripe_checkout_session_id', $session->id)
                ->update([
                    'user_id' => $user->id,
                    'user_zipcode_subscription_id' => $subscription->id,
                    'stripe_customer_id' => $customerId,
                    'stripe_subscription_id' => $subscriptionId,
                    'billing_interval' => $billingInterval,
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

            $fulfilledPaymentId = StripePayment::query()
                ->where('stripe_checkout_session_id', $session->id)
                ->value('id');
        });

        if ($fulfilledPaymentId) {
            SendWelcomeIntakeKickoffEmail::dispatch($fulfilledPaymentId);
        }
    }

    public function syncSubscriptionStatus(Subscription $subscription): void
    {
        $zipcodeId = $subscription->metadata['zipcode_id'] ?? null;
        $stripeSubscriptionId = $subscription->id;

        $localSubscription = UserZipcodeSubscription::query()
            ->where('stripe_subscription_id', $stripeSubscriptionId)
            ->first();

        if (! $localSubscription && $zipcodeId) {
            return;
        }

        if (! $localSubscription) {
            return;
        }

        $status = match ($subscription->status) {
            'active', 'trialing' => 'active',
            'canceled' => 'canceled',
            'past_due', 'unpaid' => 'expired',
            default => $localSubscription->status,
        };

        $localSubscription->update([
            'status' => $status,
        ]);

        $this->syncLocalSubscriptionPeriod($localSubscription, $subscription);

        if ($status === 'canceled' && $zipcodeId) {
            $lead = Lead::query()
                ->where('stripe_subscription_id', $stripeSubscriptionId)
                ->first();

            $lead?->update([
                'internal_comments' => trim(($lead->internal_comments ?? '')."\nStripe subscription canceled on ".now()->toDateTimeString()),
            ]);
        }

        if ($localSubscription && empty($localSubscription->billing_interval)) {
            $interval = $subscription->metadata['billing_interval'] ?? null;
            if ($interval) {
                $localSubscription->update(['billing_interval' => $interval]);
            }
        }
    }

    public function recordInvoice(Invoice $invoice): void
    {
        $subscriptionId = is_string($invoice->subscription) ? $invoice->subscription : $invoice->subscription?->id;

        if (! $subscriptionId || $invoice->status !== 'paid') {
            return;
        }

        $localSubscription = UserZipcodeSubscription::query()
            ->where('stripe_subscription_id', $subscriptionId)
            ->first();

        $billingInterval = $localSubscription?->billing_interval
            ?? ($invoice->metadata['billing_interval'] ?? Zipcode::BILLING_MONTHLY);

        StripePayment::updateOrCreate(
            ['stripe_invoice_id' => $invoice->id],
            [
                'user_id' => $localSubscription?->user_id,
                'user_zipcode_subscription_id' => $localSubscription?->id,
                'zipcode_id' => $localSubscription?->zipcode_ids[0] ?? null,
                'lead_id' => Lead::query()->where('stripe_subscription_id', $subscriptionId)->value('id'),
                'stripe_customer_id' => is_string($invoice->customer) ? $invoice->customer : $invoice->customer?->id,
                'stripe_subscription_id' => $subscriptionId,
                'stripe_payment_intent_id' => is_string($invoice->payment_intent) ? $invoice->payment_intent : $invoice->payment_intent?->id,
                'amount_cents' => (int) ($invoice->amount_paid ?? 0),
                'currency' => $invoice->currency ?? 'usd',
                'status' => 'paid',
                'billing_reason' => $invoice->billing_reason,
                'billing_interval' => $billingInterval,
                'customer_email' => $invoice->customer_email,
                'paid_at' => $invoice->status_transitions?->paid_at
                    ? now()->setTimestamp($invoice->status_transitions->paid_at)
                    : now(),
                'metadata' => $invoice->metadata?->toArray() ?? [],
            ],
        );

        if ($localSubscription) {
            $stripeSubscription = $this->retrieveStripeSubscription($subscriptionId);

            if ($stripeSubscription) {
                $this->syncLocalSubscriptionPeriod($localSubscription, $stripeSubscription);
            }
        }
    }

    public function recordFailedInvoice(Invoice $invoice): void
    {
        $subscriptionId = is_string($invoice->subscription) ? $invoice->subscription : $invoice->subscription?->id;

        if (! $subscriptionId) {
            return;
        }

        $localSubscription = UserZipcodeSubscription::query()
            ->where('stripe_subscription_id', $subscriptionId)
            ->first();

        StripePayment::updateOrCreate(
            ['stripe_invoice_id' => $invoice->id],
            [
                'user_id' => $localSubscription?->user_id,
                'user_zipcode_subscription_id' => $localSubscription?->id,
                'zipcode_id' => $localSubscription?->zipcode_ids[0] ?? null,
                'stripe_customer_id' => is_string($invoice->customer) ? $invoice->customer : $invoice->customer?->id,
                'stripe_subscription_id' => $subscriptionId,
                'amount_cents' => (int) ($invoice->amount_due ?? 0),
                'currency' => $invoice->currency ?? 'usd',
                'status' => 'failed',
                'billing_reason' => $invoice->billing_reason,
                'billing_interval' => $localSubscription?->billing_interval,
                'customer_email' => $invoice->customer_email,
                'metadata' => $invoice->metadata?->toArray() ?? [],
            ],
        );
    }

    protected function resolveCustomerUser(Lead $lead, ?string $stripeCustomerId, ?string $email): User
    {
        $user = User::query()
            ->where('email', $email ?: $lead->email)
            ->first();

        if ($user) {
            if ($stripeCustomerId && ! $user->stripe_id) {
                $user->update(['stripe_id' => $stripeCustomerId]);
            }

            return $user;
        }

        $nameParts = preg_split('/\s+/', trim($lead->name), 2) ?: [];
        $firstName = $nameParts[0] ?? 'Customer';
        $lastName = $nameParts[1] ?? '';

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $lead->name,
            'email' => $email ?: $lead->email,
            'phone' => $lead->phone,
            'password' => Hash::make(Str::random(32)),
            'role' => 'customer',
            'status' => 'active',
            'stripe_id' => $stripeCustomerId,
        ]);

        $user->sendEmailVerificationNotification();

        return $user;
    }

    protected function activateTerritorySubscription(
        User $user,
        Zipcode $zipcode,
        ?string $stripeSubscriptionId,
        ?string $stripeCustomerId,
        string $billingInterval = Zipcode::BILLING_MONTHLY,
        ?Subscription $stripeSubscription = null,
    ): UserZipcodeSubscription {
        if (! $stripeSubscription && $stripeSubscriptionId) {
            $stripeSubscription = $this->retrieveStripeSubscription($stripeSubscriptionId);
        }

        $existing = UserZipcodeSubscription::query()
            ->where('user_id', $user->id)
            ->where('stripe_subscription_id', $stripeSubscriptionId)
            ->first();

        if ($existing) {
            if ($stripeSubscription) {
                $this->syncLocalSubscriptionPeriod($existing, $stripeSubscription);
            }

            return $existing;
        }

        $period = $this->resolvePeriodDates($stripeSubscription, $billingInterval);

        return UserZipcodeSubscription::create([
            'user_id' => $user->id,
            'zipcode_ids' => [$zipcode->id],
            'start_date' => $period['start'],
            'end_date' => $period['end'],
            'status' => 'active',
            'stripe_subscription_id' => $stripeSubscriptionId,
            'stripe_customer_id' => $stripeCustomerId,
            'billing_interval' => $billingInterval,
        ]);
    }

    protected function resolveStripeSubscription(CheckoutSession $session, ?string $subscriptionId): ?Subscription
    {
        if (is_object($session->subscription)) {
            return $session->subscription;
        }

        return $subscriptionId ? $this->retrieveStripeSubscription($subscriptionId) : null;
    }

    protected function retrieveStripeSubscription(string $subscriptionId): ?Subscription
    {
        try {
            return $this->stripe->client()->subscriptions->retrieve($subscriptionId);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{start: string, end: string}
     */
    protected function resolvePeriodDates(?Subscription $stripeSubscription, string $billingInterval): array
    {
        if ($stripeSubscription instanceof Subscription) {
            $startTimestamp = $stripeSubscription->start_date ?? $stripeSubscription->current_period_start;
            $endTimestamp = $stripeSubscription->current_period_end;

            if ($stripeSubscription->cancel_at) {
                $endTimestamp = $stripeSubscription->cancel_at;
            }

            return [
                'start' => $startTimestamp
                    ? Carbon::createFromTimestamp($startTimestamp)->toDateString()
                    : now()->toDateString(),
                'end' => $endTimestamp
                    ? Carbon::createFromTimestamp($endTimestamp)->toDateString()
                    : $this->estimatePeriodEnd($billingInterval),
            ];
        }

        return [
            'start' => now()->toDateString(),
            'end' => $this->estimatePeriodEnd($billingInterval),
        ];
    }

    protected function syncLocalSubscriptionPeriod(UserZipcodeSubscription $local, Subscription $stripe): void
    {
        $updates = [];

        $subscriptionStart = $stripe->start_date ?? $stripe->current_period_start;
        if (! $local->start_date && $subscriptionStart) {
            $updates['start_date'] = Carbon::createFromTimestamp($subscriptionStart)->toDateString();
        }

        if ($stripe->cancel_at) {
            $updates['end_date'] = Carbon::createFromTimestamp($stripe->cancel_at)->toDateString();
        } elseif ($stripe->ended_at && in_array($stripe->status, ['canceled', 'incomplete_expired'], true)) {
            $updates['end_date'] = Carbon::createFromTimestamp($stripe->ended_at)->toDateString();
        } elseif ($stripe->current_period_end && in_array($stripe->status, ['active', 'trialing'], true)) {
            $updates['end_date'] = Carbon::createFromTimestamp($stripe->current_period_end)->toDateString();
        }

        if ($updates !== []) {
            $local->update($updates);
        }
    }

    protected function estimatePeriodEnd(string $billingInterval): string
    {
        return $billingInterval === Zipcode::BILLING_YEARLY
            ? now()->addYear()->toDateString()
            : now()->addMonth()->toDateString();
    }

    protected function assertZipcodeAvailable(Zipcode $zipcode): void
    {
        if (! $this->isZipcodeAvailable($zipcode)) {
            throw new \RuntimeException('This ZIP code is no longer available.');
        }
    }

    protected function isZipcodeAvailable(Zipcode $zipcode): bool
    {
        if (! $zipcode->is_active) {
            return false;
        }

        return ! UserZipcodeSubscription::active()
            ->forZipcode($zipcode->id)
            ->exists();
    }

    protected function resolveBillingIntervalFromStripe(string $subscriptionId): string
    {
        try {
            $subscription = $this->stripe->client()->subscriptions->retrieve($subscriptionId);
            $interval = $subscription->metadata['billing_interval'] ?? null;

            if ($interval === Zipcode::BILLING_YEARLY) {
                return Zipcode::BILLING_YEARLY;
            }
        } catch (\Throwable) {
            //
        }

        if (! $interval) {
            return Zipcode::BILLING_MONTHLY;
        }

        return $interval;
    }
}
