<?php

namespace App\Services;

use App\Jobs\SendCheckoutRecoveryEmail;
use App\Jobs\SendZipAvailableOnHoldReleaseEmail;
use App\Models\CheckoutHold;
use App\Models\StripePayment;
use App\Models\Waitlist;
use App\Models\Zipcode;
use Illuminate\Support\Facades\DB;

class CheckoutHoldService
{
    public function __construct(
        protected StripeService $stripe,
        protected StripeSubscriptionService $subscriptions,
    ) {}

    public function createFromPayment(StripePayment $payment, ?int $waitlistId = null): CheckoutHold
    {
        $existing = CheckoutHold::query()
            ->where('stripe_payment_id', $payment->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return CheckoutHold::create([
            'stripe_payment_id' => $payment->id,
            'zipcode_id' => $payment->zipcode_id,
            'waitlist_id' => $waitlistId,
            'status' => CheckoutHold::STATUS_ACTIVE,
            'checkout_started_at' => now(),
            'hold_expires_at' => now()->addDays(CheckoutHold::HOLD_DAYS),
        ]);
    }

    public function transferToPayment(CheckoutHold $hold, StripePayment $payment): CheckoutHold
    {
        $hold->update([
            'stripe_payment_id' => $payment->id,
            'checkout_started_at' => now(),
            'hold_expires_at' => now()->addDays(CheckoutHold::HOLD_DAYS),
            'recovery_email_sent_at' => null,
            'recovery_email_status' => null,
            'recovery_email_error' => null,
        ]);

        return $hold->fresh();
    }

    public function clearHoldOnSuccessfulPayment(StripePayment $payment): void
    {
        $query = CheckoutHold::query();

        if ($payment->zipcode_id) {
            $query->where('zipcode_id', $payment->zipcode_id);
        }

        $query->where(function ($holdQuery) use ($payment): void {
            $holdQuery->where('stripe_payment_id', $payment->id);

            if (filled($payment->customer_email)) {
                $holdQuery->orWhereHas(
                    'stripePayment',
                    fn ($paymentQuery) => $paymentQuery->where('customer_email', $payment->customer_email),
                );
            }
        });

        $holdIds = $query->pluck('id');

        if ($holdIds->isEmpty()) {
            return;
        }

        CheckoutHold::query()
            ->whereIn('id', $holdIds)
            ->update([
                'status' => CheckoutHold::STATUS_COMPLETED,
                'released_at' => now(),
                'release_reason' => 'payment_completed',
            ]);

        Waitlist::query()
            ->whereIn('zip_available_notice_sent_for_hold_id', $holdIds)
            ->update(['zip_available_notice_sent_for_hold_id' => null]);
    }

    public function findActiveHoldForCustomerAndZip(string $email, int $zipcodeId): ?CheckoutHold
    {
        if (blank($email)) {
            return null;
        }

        return CheckoutHold::query()
            ->where('zipcode_id', $zipcodeId)
            ->active()
            ->whereHas(
                'stripePayment',
                fn ($query) => $query->where('customer_email', $email),
            )
            ->first();
    }

    public function recordAbandonedCheckout(StripePayment $payment, bool $sendRecoveryEmail = true): ?CheckoutHold
    {
        if ($payment->status === 'paid') {
            return null;
        }

        $zipcode = Zipcode::query()->find($payment->zipcode_id);

        if (! $zipcode) {
            return null;
        }

        if (\App\Models\UserZipcodeSubscription::active()->forZipcode($zipcode->id)->exists()) {
            return null;
        }

        $waitlistId = filled($payment->metadata['waitlist_id'] ?? null)
            ? (int) $payment->metadata['waitlist_id']
            : null;

        $hold = $this->resolveHoldForPayment($payment);

        if (! $hold) {
            if (CheckoutHold::isZipcodeHeld($zipcode->id)) {
                return null;
            }

            $hold = $this->createFromPayment($payment, $waitlistId);
        }

        if ($payment->status !== 'checkout_abandoned') {
            $payment->update(['status' => 'checkout_abandoned']);
        }

        if ($sendRecoveryEmail && blank($hold->recovery_email_sent_at)) {
            SendCheckoutRecoveryEmail::dispatch($hold->id);
        }

        return $hold;
    }

    public function extend(CheckoutHold $hold, int $hours = CheckoutHold::EXTEND_HOURS): CheckoutHold
    {
        if (! $hold->isActive()) {
            throw new \RuntimeException('Only active holds can be extended.');
        }

        $hold->update([
            'hold_expires_at' => $hold->hold_expires_at->addHours($hours),
        ]);

        return $hold->fresh();
    }

    public function release(CheckoutHold $hold, string $reason = 'admin'): CheckoutHold
    {
        if (! $hold->isActive()) {
            throw new \RuntimeException('This hold is no longer active.');
        }

        return DB::transaction(function () use ($hold, $reason): CheckoutHold {
            $hold->update([
                'status' => CheckoutHold::STATUS_RELEASED,
                'released_at' => now(),
                'release_reason' => $reason,
            ]);

            $this->notifyWaitlistForZip($hold);

            return $hold->fresh();
        });
    }

    public function expireDueHolds(): int
    {
        $expiredCount = 0;

        CheckoutHold::query()
            ->active()
            ->where('hold_expires_at', '<=', now())
            ->with(['zipcode', 'stripePayment'])
            ->orderBy('id')
            ->chunkById(50, function ($holds) use (&$expiredCount): void {
                foreach ($holds as $hold) {
                    DB::transaction(function () use ($hold, &$expiredCount): void {
                        $hold->update([
                            'status' => CheckoutHold::STATUS_EXPIRED,
                            'released_at' => now(),
                            'release_reason' => 'expired',
                        ]);

                        $this->notifyWaitlistForZip($hold);
                        $expiredCount++;
                    });
                }
            });

        return $expiredCount;
    }

    public function resendRecoveryEmail(CheckoutHold $hold): void
    {
        if (! $hold->isActive()) {
            throw new \RuntimeException('Recovery emails can only be sent for active holds.');
        }

        if ($hold->stripePayment?->status === 'paid') {
            throw new \RuntimeException('This checkout has already been paid.');
        }

        SendCheckoutRecoveryEmail::dispatch($hold->id, force: true);
    }

    public function createRepaymentSession(CheckoutHold $hold): \Stripe\Checkout\Session
    {
        if (! $this->stripe->isEnabled()) {
            throw new \RuntimeException('Stripe checkout is not configured.');
        }

        $payment = $hold->stripePayment;

        if (! $payment || ! $hold->zipcode) {
            throw new \RuntimeException('Checkout hold is missing payment or ZIP details.');
        }

        if ($payment->status === 'paid') {
            throw new \RuntimeException('This checkout has already been paid.');
        }

        $metadata = $payment->metadata ?? [];

        return $this->subscriptions->createCheckoutSession($hold->zipcode, [
            'name' => $payment->customer_name ?? 'Customer',
            'email' => $payment->customer_email ?? '',
            'phone' => $metadata['customer_phone'] ?? null,
            'company' => $metadata['customer_company'] ?? null,
            'billing_interval' => $payment->billing_interval ?? Zipcode::BILLING_MONTHLY,
            'user_id' => $payment->user_id,
            'waitlist_id' => $hold->waitlist_id,
        ], $hold);
    }

    public function notifyWaitlistForZip(CheckoutHold $hold): void
    {
        $zipcode = $hold->zipcode;

        if (! $zipcode) {
            return;
        }

        if (\App\Models\UserZipcodeSubscription::active()->forZipcode($zipcode->id)->exists()) {
            return;
        }

        if (CheckoutHold::isZipcodeHeld($zipcode->id, $hold->id)) {
            return;
        }

        Waitlist::query()
            ->where('zip_code', $zipcode->code)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($query) use ($hold) {
                $query->whereNull('zip_available_notice_sent_for_hold_id')
                    ->orWhere('zip_available_notice_sent_for_hold_id', '!=', $hold->id);
            })
            ->orderBy('id')
            ->pluck('id')
            ->each(fn (int $waitlistId) => SendZipAvailableOnHoldReleaseEmail::dispatch($waitlistId, $hold->id));
    }

    protected function resolveHoldForPayment(StripePayment $payment): ?CheckoutHold
    {
        return CheckoutHold::query()
            ->where('stripe_payment_id', $payment->id)
            ->first();
    }
}
