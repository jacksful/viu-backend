<?php

namespace App\Services;

use App\Jobs\SendWaitlistCheckoutLinkEmail;
use App\Models\User;
use App\Models\UserZipcodeSubscription;
use App\Models\Waitlist;
use App\Models\Zipcode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WaitlistConversionService
{
    public function __construct(
        protected StripeService $stripe,
        protected StripeSubscriptionService $subscriptions,
    ) {}

    public function convert(Waitlist $waitlist): Waitlist
    {
        if (! $waitlist->canShowConvertAction()) {
            throw new \RuntimeException('This waitlist entry has already been converted or has a pending checkout link.');
        }

        $blockers = $waitlist->conversionBlockers();

        if ($blockers !== []) {
            throw new \RuntimeException(implode(' ', $blockers));
        }

        return DB::transaction(function () use ($waitlist): Waitlist {
            $zipcode = Zipcode::query()
                ->where('code', $waitlist->zip_code)
                ->where('is_active', true)
                ->firstOrFail();

            $user = $this->resolveCustomerUser($waitlist);

            $billingInterval = collect($zipcode->purchasableBillingPlans())->value('interval')
                ?? Zipcode::BILLING_MONTHLY;

            $session = $this->subscriptions->createCheckoutSession($zipcode, [
                'name' => $waitlist->name,
                'email' => $waitlist->email,
                'phone' => $waitlist->phone,
                'billing_interval' => $billingInterval,
                'user_id' => $user->id,
                'waitlist_id' => $waitlist->id,
            ]);

            $payment = \App\Models\StripePayment::query()
                ->where('stripe_checkout_session_id', $session->id)
                ->first();

            $lockedUntil = now()->addDays(\App\Models\CheckoutHold::HOLD_DAYS);

            $waitlist->update([
                'converted_to_user_id' => $user->id,
                'converted_at' => now(),
                'stripe_payment_id' => $payment?->id,
                'checkout_url' => $session->url,
                'locked_until' => $lockedUntil,
                'status' => 'contacted',
            ]);

            SendWaitlistCheckoutLinkEmail::dispatch($waitlist->id);

            return $waitlist->fresh(['convertedToUser', 'stripePayment']);
        });
    }

    protected function resolveCustomerUser(Waitlist $waitlist): User
    {
        $existingUser = User::query()->where('email', $waitlist->email)->first();

        if ($existingUser) {
            if ($existingUser->role !== 'customer') {
                throw new \RuntimeException('A non-customer account already exists for this email address.');
            }

            $updates = [];

            if (blank($existingUser->phone) && filled($waitlist->phone)) {
                $updates['phone'] = $waitlist->phone;
            }

            if ($updates !== []) {
                $existingUser->update($updates);
            }

            return $existingUser;
        }

        $nameParts = preg_split('/\s+/', trim($waitlist->name), 2) ?: [];
        $firstName = $nameParts[0] ?? 'Customer';
        $lastName = $nameParts[1] ?? '';

        return User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => trim($waitlist->name),
            'email' => $waitlist->email,
            'phone' => $waitlist->phone,
            'password' => Hash::make(Str::random(32)),
            'role' => 'customer',
            'status' => 'active',
        ]);
    }
}
