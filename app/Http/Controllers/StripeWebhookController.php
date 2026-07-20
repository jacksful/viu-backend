<?php

namespace App\Http\Controllers;

use App\Models\StripeWebhookEvent;
use App\Services\StripeService;
use App\Services\StripeSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeService $stripe,
        protected StripeSubscriptionService $subscriptions,
    ) {}

    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret') ?: $this->stripe->settings()->webhook_secret;

        if (! filled($secret)) {
            return response('Stripe webhook secret is not configured.', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (\UnexpectedValueException|SignatureVerificationException) {
            return response('Invalid payload or signature.', 400);
        }

        if (StripeWebhookEvent::alreadyProcessed($event->id)) {
            return response('Event already processed.', 200);
        }

        $this->handleEvent($event);
        StripeWebhookEvent::markProcessed($event->id, $event->type);

        return response('Webhook handled.', 200);
    }

    protected function handleEvent(Event $event): void
    {
        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event->data->object),
            'checkout.session.expired' => $this->subscriptions->handleCheckoutSessionExpired($event->data->object),
            'checkout.session.async_payment_succeeded' => $this->handleCheckoutSessionCompleted($event->data->object),
            'checkout.session.async_payment_failed' => $this->subscriptions->handleCheckoutSessionFailed($event->data->object),
            'customer.subscription.updated' => $this->subscriptions->syncSubscriptionStatus($event->data->object),
            'customer.subscription.deleted' => $this->subscriptions->syncSubscriptionStatus($event->data->object),
            'invoice.payment_succeeded' => $this->subscriptions->recordInvoice($event->data->object),
            'invoice.payment_failed' => $this->subscriptions->recordFailedInvoice($event->data->object),
            default => null,
        };
    }

    protected function handleCheckoutSessionCompleted(\Stripe\Checkout\Session $session): void
    {
        if ($session->payment_status === 'paid') {
            $this->subscriptions->fulfillCheckoutSession($session);

            return;
        }

        $this->subscriptions->handleCheckoutSessionFailed($session);
    }
}
