<?php

namespace App\Jobs;

use App\Mail\WelcomeIntakeKickoffMail;
use App\Models\EmailSetting;
use App\Models\StripePayment;
use App\Support\IntakeUrl;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendWelcomeIntakeKickoffEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $stripePaymentId,
    ) {}

    public function uniqueId(): string
    {
        return 'welcome-intake-kickoff:'.$this->stripePaymentId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $payment = StripePayment::query()
            ->with(['user', 'zipcode', 'subscription'])
            ->find($this->stripePaymentId);

        if (! $payment || $payment->status !== 'paid') {
            return;
        }

        $metadata = $payment->metadata ?? [];

        if (! empty($metadata['welcome_email_sent_at'])) {
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

        $intakeUrl = $payment->subscription
            ? IntakeUrl::forSubscription($payment->subscription)
            : URL::route('home');
        $calendarUrl = config('viu.calendar_url') ?: 'mailto:'.(config('mail.from.address') ?: 'support@fullviu.com');
        $unsubscribeUrl = config('viu.unsubscribe_url') ?: 'mailto:'.(config('mail.from.address') ?: 'support@fullviu.com');

        try {
            Mail::to($recipient)->send(new WelcomeIntakeKickoffMail(
                firstName: $firstName,
                zipCode: $zipcode->code,
                intakeUrl: $intakeUrl,
                calendarUrl: $calendarUrl,
                unsubscribeUrl: $unsubscribeUrl,
            ));

            $payment->update([
                'metadata' => array_merge($metadata, [
                    'welcome_email_sent_at' => now()->toIso8601String(),
                ]),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send welcome intake kickoff email.', [
                'stripe_payment_id' => $payment->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
