<?php

namespace App\Http\Controllers;

use App\Models\StripePayment;
use App\Models\UserZipcodeSubscription;
use App\Services\StripeService;
use App\Services\StripeSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\PaymentMethod;

class StripeCheckoutController extends Controller
{
    public function __construct(
        protected StripeService $stripe,
        protected StripeSubscriptionService $subscriptions,
    ) {}

    public function create(Request $request): JsonResponse
    {
        if (! $this->stripe->isEnabled()) {
            return response()->json([
                'message' => 'Online payments are not available yet. Please contact us to claim this territory.',
            ], 503);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'zipcode_id' => ['required', 'integer', 'exists:zipcodes,id'],
            'billing_interval' => ['required', 'string', 'in:month,year'],
        ]);

        $zipcode = \App\Models\Zipcode::query()
            ->where('id', $validated['zipcode_id'])
            ->where('is_active', true)
            ->first();

        if (! $zipcode) {
            return response()->json([
                'message' => 'ZIP code not available in our coverage area.',
            ], 422);
        }

        if (! $zipcode->hasPurchasablePlans()) {
            return response()->json([
                'message' => 'This ZIP code does not have Stripe subscription pricing configured yet.',
            ], 422);
        }

        if (! $zipcode->hasStripePriceForInterval($validated['billing_interval'])) {
            return response()->json([
                'message' => 'The selected billing plan is not available for online checkout yet.',
            ], 422);
        }

        try {
            $session = $this->subscriptions->createCheckoutSession($zipcode, [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company' => $validated['company'] ?? null,
                'billing_interval' => $validated['billing_interval'],
            ]);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'checkout_url' => $session->url,
            'session_id' => $session->id,
        ]);
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $details = null;

        if ($sessionId && $this->stripe->isEnabled()) {
            try {
                $session = $this->stripe->client()->checkout->sessions->retrieve($sessionId, [
                    'expand' => [
                        'payment_intent.payment_method',
                        'subscription',
                        'subscription.latest_invoice.payment_intent.payment_method',
                    ],
                ]);
                $this->subscriptions->fulfillCheckoutSession($session);
                $details = $this->buildSuccessDetails($session);
            } catch (\Throwable) {
                // Webhook remains the source of truth; success page is best-effort.
            }
        }

        if (! $details && $sessionId) {
            $payment = StripePayment::query()
                ->where('stripe_checkout_session_id', $sessionId)
                ->first();

            if ($payment) {
                $details = $this->buildSuccessDetailsFromPayment($payment);
            }
        }

        return view('stripe.checkout-success', [
            'details' => $details,
        ]);
    }

    /**
     * @return array{
     *     order_number: string,
     *     amount: string,
     *     payment_method: string,
     *     transaction_id: string,
     *     subscription_start: string,
     *     subscription_end: string
     * }|null
     */
    private function buildSuccessDetails(CheckoutSession $session): ?array
    {
        $payment = StripePayment::query()
            ->where('stripe_checkout_session_id', $session->id)
            ->first();

        if (! $payment) {
            return null;
        }

        $payment->refresh()->loadMissing('subscription');

        $paymentIntentId = is_object($session->payment_intent)
            ? $session->payment_intent->id
            : (is_string($session->payment_intent) ? $session->payment_intent : $payment->stripe_payment_intent_id);

        return array_merge([
            'order_number' => '#'.$payment->id,
            'amount' => $this->formatMoney($session->amount_total ?? $payment->amount_cents, $session->currency ?? $payment->currency),
            'payment_method' => $this->resolvePaymentMethodFromSession($session) ?? 'Card',
            'transaction_id' => $paymentIntentId ?? $session->id,
        ], $this->subscriptionDateFields($payment->subscription));
    }

    /**
     * @return array{
     *     order_number: string,
     *     amount: string,
     *     payment_method: string,
     *     transaction_id: string,
     *     subscription_start: string,
     *     subscription_end: string
     * }
     */
    private function buildSuccessDetailsFromPayment(StripePayment $payment): array
    {
        $payment->loadMissing('subscription');

        return array_merge([
            'order_number' => '#'.$payment->id,
            'amount' => $this->formatMoney($payment->amount_cents, $payment->currency),
            'payment_method' => 'Card',
            'transaction_id' => $payment->stripe_payment_intent_id ?? $payment->stripe_checkout_session_id,
        ], $this->subscriptionDateFields($payment->subscription));
    }

    /**
     * @return array{subscription_start: string, subscription_end: string}
     */
    private function subscriptionDateFields(?UserZipcodeSubscription $subscription): array
    {
        return [
            'subscription_start' => $subscription?->formattedStartDate() ?? '—',
            'subscription_end' => $subscription?->formattedEndDate() ?? 'Ongoing',
        ];
    }

    private function resolvePaymentMethodFromSession(CheckoutSession $session): ?string
    {
        if (is_object($session->payment_intent) && is_object($session->payment_intent->payment_method)) {
            return $this->formatPaymentMethod($session->payment_intent->payment_method);
        }

        $subscription = $session->subscription;
        if (! is_object($subscription)) {
            return null;
        }

        $invoice = $subscription->latest_invoice ?? null;
        if (! is_object($invoice)) {
            return null;
        }

        $paymentIntent = $invoice->payment_intent ?? null;
        if (is_object($paymentIntent) && is_object($paymentIntent->payment_method)) {
            return $this->formatPaymentMethod($paymentIntent->payment_method);
        }

        return null;
    }

    private function formatPaymentMethod(PaymentMethod $paymentMethod): ?string
    {
        $card = $paymentMethod->card ?? null;

        if (! $card) {
            return null;
        }

        $brand = ucfirst((string) ($card->brand ?? 'Card'));
        $last4 = (string) ($card->last4 ?? '');

        return $last4 !== '' ? "{$brand} ending in {$last4}" : $brand;
    }

    private function formatMoney(?int $amountCents, ?string $currency): string
    {
        if ($amountCents === null) {
            return '—';
        }

        $formatted = number_format($amountCents / 100, 2);

        return strtoupper($currency ?? 'USD') === 'USD'
            ? '$'.$formatted
            : strtoupper($currency ?? 'USD').' '.$formatted;
    }
}
