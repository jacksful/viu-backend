<?php

namespace App\Jobs;

use App\Mail\InquiryAcknowledgmentMail;
use App\Models\CheckoutHold;
use App\Models\EmailSetting;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCheckoutRecoveryEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $holdId,
        public bool $force = false,
    ) {}

    public function uniqueId(): string
    {
        return 'checkout-recovery:'.$this->holdId.($this->force ? ':force:'.now()->timestamp : '');
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $hold = CheckoutHold::query()
            ->with(['stripePayment', 'zipcode'])
            ->find($this->holdId);

        if (! $hold || ! $hold->isActive()) {
            return;
        }

        $payment = $hold->stripePayment;

        if (! $payment || blank($payment->customer_email)) {
            return;
        }

        if ($payment->status === 'paid') {
            return;
        }

        if (! $this->force && filled($hold->recovery_email_sent_at)) {
            return;
        }

        $checkoutUrl = $this->resolveCheckoutUrl($payment);

        if (blank($checkoutUrl)) {
            $hold->update([
                'recovery_email_status' => CheckoutHold::RECOVERY_STATUS_FAILED,
                'recovery_email_error' => 'Checkout URL is no longer available.',
            ]);

            return;
        }

        $firstName = filled($payment->customer_name)
            ? (explode(' ', trim($payment->customer_name), 2)[0] ?: 'there')
            : 'there';

        $amount = '$'.number_format($payment->amount_cents / 100, 2);
        $unsubscribeUrl = config('viu.unsubscribe_url')
            ?: 'mailto:'.(config('mail.from.address') ?: 'support@fullviu.com');

        try {
            Mail::to($payment->customer_email)->send(new InquiryAcknowledgmentMail(
                firstName: $firstName,
                zipCode: (string) ($hold->zipcode?->code ?? ''),
                amount: $amount,
                checkoutUrl: $checkoutUrl,
                unsubscribeUrl: $unsubscribeUrl,
            ));

            $hold->update([
                'recovery_email_sent_at' => now(),
                'recovery_email_status' => CheckoutHold::RECOVERY_STATUS_SENT,
                'recovery_email_error' => null,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send checkout recovery email.', [
                'hold_id' => $hold->id,
                'recipient' => $payment->customer_email,
                'error' => $exception->getMessage(),
            ]);

            $hold->update([
                'recovery_email_status' => CheckoutHold::RECOVERY_STATUS_FAILED,
                'recovery_email_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function resolveCheckoutUrl(\App\Models\StripePayment $payment): ?string
    {
        if (blank($payment->stripe_checkout_session_id)) {
            return null;
        }

        try {
            $session = app(\App\Services\StripeService::class)
                ->client()
                ->checkout
                ->sessions
                ->retrieve($payment->stripe_checkout_session_id);

            if ($session->status === 'open' && filled($session->url)) {
                return $session->url;
            }
        } catch (Throwable) {
            //
        }

        return null;
    }
}
