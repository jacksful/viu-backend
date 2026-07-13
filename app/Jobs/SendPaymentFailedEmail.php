<?php

namespace App\Jobs;

use App\Mail\PaymentFailedMail;
use App\Models\EmailSetting;
use App\Models\StripePayment;
use App\Models\Zipcode;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendPaymentFailedEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $stripePaymentId,
    ) {}

    public function uniqueId(): string
    {
        return 'payment-failed:'.$this->stripePaymentId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $payment = StripePayment::query()
            ->with(['user', 'zipcode'])
            ->find($this->stripePaymentId);

        if (! $payment || $payment->status !== 'failed') {
            return;
        }

        $metadata = $payment->metadata ?? [];

        if (! empty($metadata['payment_failed_email_sent_at'])) {
            return;
        }

        $zipcode = $payment->zipcode;

        if (! $zipcode) {
            return;
        }

        $recipient = $payment->user?->email ?: $payment->customer_email;

        if (blank($recipient)) {
            return;
        }

        $user = $payment->user;
        $firstName = $user && filled($user->first_name)
            ? $user->first_name
            : (explode(' ', trim($payment->customer_name ?? ''), 2)[0] ?: 'there');

        $billingInterval = $payment->billing_interval ?: Zipcode::BILLING_MONTHLY;
        $billingIntervalLabel = $billingInterval === Zipcode::BILLING_YEARLY ? 'yearly' : 'monthly';
        $amount = $this->formatAmount($payment->amount_cents, $payment->currency);
        $billingPortalUrl = URL::route('user.dashboard');

        try {
            Mail::to($recipient)->send(new PaymentFailedMail(
                firstName: $firstName,
                zipCode: $zipcode->code,
                amount: $amount,
                billingIntervalLabel: $billingIntervalLabel,
                billingPortalUrl: $billingPortalUrl,
            ));

            $payment->update([
                'metadata' => array_merge($metadata, [
                    'payment_failed_email_sent_at' => now()->toIso8601String(),
                ]),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send payment failed email.', [
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
}
