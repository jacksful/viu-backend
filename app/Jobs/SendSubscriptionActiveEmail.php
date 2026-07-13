<?php

namespace App\Jobs;

use App\Mail\SubscriptionActiveMail;
use App\Models\EmailSetting;
use App\Models\StripePayment;
use App\Models\Zipcode;
use App\Services\StripeService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendSubscriptionActiveEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $stripePaymentId,
    ) {}

    public function uniqueId(): string
    {
        return 'subscription-active:'.$this->stripePaymentId;
    }

    public function handle(StripeService $stripe): void
    {
        EmailSetting::applyMailConfig();

        $payment = StripePayment::query()
            ->with(['user', 'zipcode', 'subscription'])
            ->find($this->stripePaymentId);

        if (! $payment || $payment->status !== 'paid') {
            return;
        }

        $metadata = $payment->metadata ?? [];

        if (! empty($metadata['payment_confirmation_email_sent_at'])) {
            return;
        }

        $user = $payment->user;
        $zipcode = $payment->zipcode;

        if (! $user || ! $zipcode) {
            return;
        }

        $recipient = $user->email ?: $payment->customer_email;

        if (blank($recipient)) {
            return;
        }

        $firstName = filled($user->first_name)
            ? $user->first_name
            : (explode(' ', trim($payment->customer_name ?? ''), 2)[0] ?: 'there');

        $billingInterval = $payment->billing_interval ?: Zipcode::BILLING_MONTHLY;
        $billingIntervalLabel = $billingInterval === Zipcode::BILLING_YEARLY ? 'yearly' : 'monthly';
        $amount = $this->formatAmount($payment->amount_cents, $payment->currency);
        $paidDate = ($payment->paid_at ?? now())->format('M j, Y');
        $nextRenewalDate = $payment->subscription?->formattedEndDate() ?? 'Ongoing';
        $dashboardUrl = URL::route('user.dashboard');
        $billingPortalUrl = URL::route('user.dashboard');
        $receiptPdfUrl = $this->resolveReceiptPdfUrl($stripe, $payment);

        try {
            Mail::to($recipient)->send(new SubscriptionActiveMail(
                firstName: $firstName,
                zipCode: $zipcode->code,
                amount: $amount,
                paidDate: $paidDate,
                nextRenewalDate: $nextRenewalDate,
                billingIntervalLabel: $billingIntervalLabel,
                dashboardUrl: $dashboardUrl,
                billingPortalUrl: $billingPortalUrl,
                receiptPdfUrl: $receiptPdfUrl,
            ));

            $payment->update([
                'metadata' => array_merge($metadata, [
                    'payment_confirmation_email_sent_at' => now()->toIso8601String(),
                ]),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send subscription active email.', [
                'stripe_payment_id' => $payment->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function formatAmount(int $amountCents, ?string $currency): string
    {
        $formatted = number_format($amountCents / 100, 2);

        return strtoupper($currency ?? 'USD') === 'USD'
            ? '$'.$formatted
            : strtoupper($currency ?? 'USD').' '.$formatted;
    }

    protected function resolveReceiptPdfUrl(StripeService $stripe, StripePayment $payment): ?string
    {
        if (! $stripe->isEnabled()) {
            return null;
        }

        $invoiceId = $payment->stripe_invoice_id;

        if (! $invoiceId && $payment->stripe_subscription_id) {
            try {
                $subscription = $stripe->client()->subscriptions->retrieve(
                    $payment->stripe_subscription_id,
                    ['expand' => ['latest_invoice']],
                );

                $latestInvoice = $subscription->latest_invoice ?? null;
                $invoiceId = is_object($latestInvoice)
                    ? $latestInvoice->id
                    : (is_string($latestInvoice) ? $latestInvoice : null);
            } catch (Throwable) {
                return null;
            }
        }

        if (! $invoiceId) {
            return null;
        }

        try {
            $invoice = $stripe->client()->invoices->retrieve($invoiceId);

            return filled($invoice->invoice_pdf) ? $invoice->invoice_pdf : null;
        } catch (Throwable) {
            return null;
        }
    }
}
