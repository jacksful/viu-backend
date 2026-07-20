<?php

namespace App\Services;

use App\Jobs\SendCancellationConfirmationEmail;
use App\Jobs\SendPaymentFailedEmail;
use App\Jobs\SendSubscriptionActiveEmail;
use App\Jobs\SendWelcomeIntakeKickoffEmail;
use App\Models\CheckoutHold;
use App\Models\StripePayment;
use App\Models\User;
use App\Models\UserZipcodeSubscription;
use App\Models\Waitlist;
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
     * @param  array{name: string, email: string, phone?: string|null, company?: string|null, billing_interval?: string|null, user_id?: int|null, waitlist_id?: int|null}  $customer
     */
    public function createCheckoutSession(Zipcode $zipcode, array $customer, ?CheckoutHold $existingHold = null): CheckoutSession
    {
        if (! $this->stripe->isEnabled()) {
            throw new \RuntimeException('Stripe payments are not enabled.');
        }

        $existingHold ??= app(CheckoutHoldService::class)->findActiveHoldForCustomerAndZip(
            $customer['email'],
            $zipcode->id,
        );

        $this->assertZipcodeAvailable(
            $zipcode,
            $existingHold?->id,
            $customer['waitlist_id'] ?? $existingHold?->waitlist_id,
        );

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

        $payment = StripePayment::create([
            'user_id' => $customer['user_id'] ?? null,
            'zipcode_id' => $zipcode->id,
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
                'customer_phone' => $customer['phone'] ?? '',
                'customer_company' => $customer['company'] ?? '',
                'waitlist_id' => isset($customer['waitlist_id']) ? (string) $customer['waitlist_id'] : '',
            ],
        ]);

        $session = $this->stripe->client()->checkout->sessions->create([
            'mode' => 'subscription',
            'customer_email' => $customer['email'],
            'line_items' => [$lineItem],
            'metadata' => [
                'stripe_payment_id' => (string) $payment->id,
                'zipcode_id' => (string) $zipcode->id,
                'billing_interval' => $plan['interval'],
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'] ?? '',
                'waitlist_id' => isset($customer['waitlist_id']) ? (string) $customer['waitlist_id'] : '',
            ],
            'subscription_data' => [
                'metadata' => [
                    'stripe_payment_id' => (string) $payment->id,
                    'zipcode_id' => (string) $zipcode->id,
                    'billing_interval' => $plan['interval'],
                    'waitlist_id' => isset($customer['waitlist_id']) ? (string) $customer['waitlist_id'] : '',
                ],
            ],
            'success_url' => $settings->resolvedSuccessUrl().'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.checkout.cancel').'?session_id={CHECKOUT_SESSION_ID}',
        ]);

        $payment->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        if ($existingHold) {
            app(CheckoutHoldService::class)->transferToPayment($existingHold, $payment);
        }

        return $session;
    }

    public function fulfillCheckoutSession(CheckoutSession $session): void
    {
        if ($session->mode !== 'subscription' || $session->payment_status !== 'paid') {
            return;
        }

        $metadata = $session->metadata?->toArray() ?? [];
        $payment = $this->resolveCheckoutPayment($session, $metadata);

        if (! $payment) {
            return;
        }

        $zipcodeId = $payment->zipcode_id ?? ($metadata['zipcode_id'] ?? null);
        $zipcode = $zipcodeId ? Zipcode::query()->find($zipcodeId) : null;

        if (! $zipcode) {
            return;
        }

        if ($payment->status === 'paid' && $payment->user_id) {
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

            app(CheckoutHoldService::class)->clearHoldOnSuccessfulPayment($payment);

            return;
        }

        $subscriptionId = is_string($session->subscription) ? $session->subscription : $session->subscription?->id;
        $customerId = is_string($session->customer) ? $session->customer : $session->customer?->id;
        $billingInterval = $metadata['billing_interval']
            ?? $payment->billing_interval
            ?? ($subscriptionId ? $this->resolveBillingIntervalFromStripe($subscriptionId) : Zipcode::BILLING_MONTHLY);
        $stripeSubscription = $this->resolveStripeSubscription($session, $subscriptionId);

        $customer = [
            'name' => $payment->customer_name ?? $metadata['customer_name'] ?? 'Customer',
            'email' => $payment->customer_email ?? $session->customer_email ?? '',
            'phone' => $payment->metadata['customer_phone'] ?? $metadata['customer_phone'] ?? null,
        ];

        $fulfilledPaymentId = null;

        [$exceptHoldId, $exceptWaitlistId] = $this->resolveFulfillmentExceptions($payment, $zipcode, $customer, $metadata);

        DB::transaction(function () use ($payment, $zipcode, $session, $subscriptionId, $customerId, $billingInterval, $stripeSubscription, $customer, $exceptHoldId, $exceptWaitlistId, &$fulfilledPaymentId): void {
            if (! $this->isZipcodeAvailable($zipcode, $exceptHoldId, $exceptWaitlistId)) {
                if ($subscriptionId) {
                    $this->stripe->client()->subscriptions->cancel($subscriptionId);
                }

                $payment->update(['status' => 'cancelled_unavailable']);

                return;
            }

            $user = $this->resolveCustomerUser($customer, $customerId, $customer['email']);
            $subscription = $this->activateTerritorySubscription(
                $user,
                $zipcode,
                $subscriptionId,
                $customerId,
                $billingInterval,
                $stripeSubscription,
            );

            $payment->update([
                'user_id' => $user->id,
                'user_zipcode_subscription_id' => $subscription->id,
                'stripe_customer_id' => $customerId,
                'stripe_subscription_id' => $subscriptionId,
                'billing_interval' => $billingInterval,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $fulfilledPaymentId = $payment->id;
        });

        if ($fulfilledPaymentId) {
            app(CheckoutHoldService::class)->clearHoldOnSuccessfulPayment($payment);
            $this->releaseWaitlistLock($metadata, $payment);
            SendWelcomeIntakeKickoffEmail::dispatch($fulfilledPaymentId);
        }
    }

    public function handleCheckoutSessionExpired(CheckoutSession $session): void
    {
        $payment = $this->resolveCheckoutPayment($session, $session->metadata?->toArray() ?? []);

        if (! $payment || $payment->status === 'paid') {
            return;
        }

        app(CheckoutHoldService::class)->recordAbandonedCheckout($payment);
    }

    public function handleCheckoutCancelled(CheckoutSession $session): void
    {
        $payment = $this->resolveCheckoutPayment($session, $session->metadata?->toArray() ?? []);

        if (! $payment || $payment->status === 'paid') {
            return;
        }

        app(CheckoutHoldService::class)->recordAbandonedCheckout($payment);
    }

    public function handleCheckoutSessionFailed(CheckoutSession $session): void
    {
        $payment = $this->resolveCheckoutPayment($session, $session->metadata?->toArray() ?? []);

        if (! $payment || $payment->status === 'paid') {
            return;
        }

        app(CheckoutHoldService::class)->recordAbandonedCheckout($payment);
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

        $wasScheduledToCancel = (bool) $localSubscription->cancel_at_period_end;
        $cancelAtPeriodEnd = (bool) ($subscription->cancel_at_period_end ?? false);

        $localSubscription->update([
            'status' => $status,
            'cancel_at_period_end' => $cancelAtPeriodEnd,
        ]);

        $this->syncLocalSubscriptionPeriod($localSubscription, $subscription);

        if (! $wasScheduledToCancel && $cancelAtPeriodEnd) {
            SendCancellationConfirmationEmail::dispatch($localSubscription->id);
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

        $existingPayment = StripePayment::query()
            ->where('stripe_invoice_id', $invoice->id)
            ->first();

        $metadata = array_merge(
            $existingPayment?->metadata ?? [],
            $invoice->metadata?->toArray() ?? [],
        );

        $payment = StripePayment::updateOrCreate(
            ['stripe_invoice_id' => $invoice->id],
            [
                'user_id' => $localSubscription?->user_id,
                'user_zipcode_subscription_id' => $localSubscription?->id,
                'zipcode_id' => $localSubscription?->zipcode_ids[0] ?? null,
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
                'metadata' => $metadata,
            ],
        );

        if ($localSubscription) {
            $stripeSubscription = $this->retrieveStripeSubscription($subscriptionId);

            if ($stripeSubscription) {
                $this->syncLocalSubscriptionPeriod($localSubscription, $stripeSubscription);
            }
        }

        if ($invoice->billing_reason === 'subscription_cycle') {
            SendSubscriptionActiveEmail::dispatch($payment->id);
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

        $existingPayment = StripePayment::query()
            ->where('stripe_invoice_id', $invoice->id)
            ->first();

        $metadata = array_merge(
            $existingPayment?->metadata ?? [],
            $invoice->metadata?->toArray() ?? [],
        );

        $payment = StripePayment::updateOrCreate(
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
                'metadata' => $metadata,
            ],
        );

        SendPaymentFailedEmail::dispatch($payment->id);
    }

    public function cancelAtPeriodEnd(UserZipcodeSubscription $subscription): void
    {
        if (! $this->stripe->isEnabled()) {
            throw new \RuntimeException('Stripe payments are not enabled.');
        }

        if (! $subscription->stripe_subscription_id) {
            throw new \RuntimeException('This subscription is not linked to Stripe.');
        }

        if ($subscription->status !== 'active') {
            throw new \RuntimeException('Only active subscriptions can be canceled.');
        }

        if ($subscription->cancel_at_period_end) {
            throw new \RuntimeException('This subscription is already scheduled to cancel.');
        }

        $stripeSubscription = $this->stripe->client()->subscriptions->update(
            $subscription->stripe_subscription_id,
            ['cancel_at_period_end' => true],
        );

        $subscription->update(['cancel_at_period_end' => true]);
        $this->syncLocalSubscriptionPeriod($subscription, $stripeSubscription);

        SendCancellationConfirmationEmail::dispatch($subscription->id);
    }

    public function reactivateSubscription(UserZipcodeSubscription $subscription): void
    {
        if (! $this->stripe->isEnabled()) {
            throw new \RuntimeException('Stripe payments are not enabled.');
        }

        if (! $subscription->stripe_subscription_id) {
            throw new \RuntimeException('This subscription is not linked to Stripe.');
        }

        if (! $subscription->cancel_at_period_end) {
            throw new \RuntimeException('This subscription is not scheduled to cancel.');
        }

        $stripeSubscription = $this->stripe->client()->subscriptions->update(
            $subscription->stripe_subscription_id,
            ['cancel_at_period_end' => false],
        );

        $subscription->update([
            'cancel_at_period_end' => false,
            'cancellation_confirmation_sent_at' => null,
        ]);
        $this->syncLocalSubscriptionPeriod($subscription, $stripeSubscription);
    }

    public function upgradeBillingInterval(UserZipcodeSubscription $subscription, string $newInterval): void
    {
        if (! $this->stripe->isEnabled()) {
            throw new \RuntimeException('Stripe payments are not enabled.');
        }

        if (! $subscription->stripe_subscription_id) {
            throw new \RuntimeException('This subscription is not linked to Stripe.');
        }

        if ($subscription->status !== 'active') {
            throw new \RuntimeException('Only active subscriptions can be upgraded.');
        }

        if ($subscription->cancel_at_period_end) {
            throw new \RuntimeException('Reactivate your subscription before changing plans.');
        }

        $newInterval = $newInterval === Zipcode::BILLING_YEARLY
            ? Zipcode::BILLING_YEARLY
            : Zipcode::BILLING_MONTHLY;

        if ($subscription->billing_interval === $newInterval) {
            throw new \RuntimeException('You are already on this billing plan.');
        }

        $zipcodeId = $subscription->zipcode_ids[0] ?? null;
        $zipcode = $zipcodeId ? Zipcode::query()->find($zipcodeId) : null;

        if (! $zipcode) {
            throw new \RuntimeException('ZIP code not found for this subscription.');
        }

        $stripePriceId = $zipcode->assertStripePriceForInterval($newInterval);

        $stripeSubscription = $this->retrieveStripeSubscription($subscription->stripe_subscription_id);

        if (! $stripeSubscription || empty($stripeSubscription->items->data)) {
            throw new \RuntimeException('Unable to load Stripe subscription details.');
        }

        $subscriptionItemId = $stripeSubscription->items->data[0]->id;
        $metadata = $stripeSubscription->metadata?->toArray() ?? [];

        $updatedStripeSubscription = $this->stripe->client()->subscriptions->update(
            $subscription->stripe_subscription_id,
            [
                'items' => [
                    ['id' => $subscriptionItemId, 'price' => $stripePriceId],
                ],
                'proration_behavior' => 'create_prorations',
                'metadata' => [
                    ...$metadata,
                    'billing_interval' => $newInterval,
                ],
            ],
        );

        $subscription->update(['billing_interval' => $newInterval]);
        $this->syncLocalSubscriptionPeriod($subscription, $updatedStripeSubscription);
    }

    /**
     * @param  array{name: string, email: string, phone?: string|null}  $customer
     */
    protected function resolveCustomerUser(array $customer, ?string $stripeCustomerId, ?string $email): User
    {
        $user = User::query()
            ->where('email', $email ?: $customer['email'])
            ->first();

        if ($user) {
            if ($stripeCustomerId && ! $user->stripe_id) {
                $user->update(['stripe_id' => $stripeCustomerId]);
            }

            return $user;
        }

        $nameParts = preg_split('/\s+/', trim($customer['name']), 2) ?: [];
        $firstName = $nameParts[0] ?? 'Customer';
        $lastName = $nameParts[1] ?? '';

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $customer['name'],
            'email' => $email ?: $customer['email'],
            'phone' => $customer['phone'] ?? null,
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

    protected function resolveCheckoutPayment(CheckoutSession $session, array $metadata): ?StripePayment
    {
        if ($paymentId = $metadata['stripe_payment_id'] ?? null) {
            $payment = StripePayment::query()->find($paymentId);

            if ($payment) {
                return $payment;
            }
        }

        return StripePayment::query()
            ->where('stripe_checkout_session_id', $session->id)
            ->first();
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

    protected function assertZipcodeAvailable(Zipcode $zipcode, ?int $exceptHoldId = null, ?int $exceptWaitlistId = null): void
    {
        if (! $this->isZipcodeAvailable($zipcode, $exceptHoldId, $exceptWaitlistId)) {
            throw new \RuntimeException('This ZIP code is no longer available.');
        }
    }

    protected function isZipcodeAvailable(Zipcode $zipcode, ?int $exceptHoldId = null, ?int $exceptWaitlistId = null): bool
    {
        if (! $zipcode->is_active) {
            return false;
        }

        if (UserZipcodeSubscription::active()->forZipcode($zipcode->id)->exists()) {
            return false;
        }

        if (CheckoutHold::isZipcodeHeld($zipcode->id, $exceptHoldId)) {
            return false;
        }

        return ! Waitlist::isZipcodeLocked($zipcode->code, $exceptWaitlistId, $exceptHoldId);
    }

    /**
     * @param  array{name: string, email: string, phone?: string|null}  $customer
     * @param  array<string, mixed>  $metadata
     * @return array{0: ?int, 1: ?int}
     */
    protected function resolveFulfillmentExceptions(
        StripePayment $payment,
        Zipcode $zipcode,
        array $customer,
        array $metadata,
    ): array {
        $holdService = app(CheckoutHoldService::class);

        $exceptHoldId = $holdService->findActiveHoldForCustomerAndZip($customer['email'], $zipcode->id)?->id
            ?? CheckoutHold::query()
                ->where('stripe_payment_id', $payment->id)
                ->where('status', CheckoutHold::STATUS_ACTIVE)
                ->value('id');

        $exceptWaitlistId = null;
        $waitlistIdRaw = $metadata['waitlist_id'] ?? $payment->metadata['waitlist_id'] ?? null;

        if (filled($waitlistIdRaw)) {
            $exceptWaitlistId = (int) $waitlistIdRaw;
        } elseif ($exceptHoldId) {
            $exceptWaitlistId = CheckoutHold::query()
                ->whereKey($exceptHoldId)
                ->value('waitlist_id');
        }

        return [$exceptHoldId, $exceptWaitlistId];
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function releaseWaitlistLock(array $metadata, StripePayment $payment): void
    {
        $waitlistId = $metadata['waitlist_id'] ?? $payment->metadata['waitlist_id'] ?? null;

        if (blank($waitlistId)) {
            return;
        }

        Waitlist::query()
            ->whereKey($waitlistId)
            ->update([
                'locked_until' => null,
                'status' => 'archived',
            ]);
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
