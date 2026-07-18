<?php

namespace App\Jobs;

use App\Mail\InquiryAcknowledgmentMail;
use App\Models\EmailSetting;
use App\Models\Waitlist;
use App\Models\Zipcode;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWaitlistCheckoutLinkEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $waitlistId,
    ) {}

    public function uniqueId(): string
    {
        return 'waitlist-checkout-link:'.$this->waitlistId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $waitlist = Waitlist::query()
            ->with('stripePayment')
            ->find($this->waitlistId);

        if (! $waitlist || blank($waitlist->email) || blank($waitlist->checkout_url)) {
            return;
        }

        $zipcode = Zipcode::query()->where('code', $waitlist->zip_code)->first();
        $firstName = filled($waitlist->name)
            ? (explode(' ', trim($waitlist->name), 2)[0] ?: 'there')
            : 'there';
        $amount = '$'.number_format((float) ($zipcode?->monthly_price ?? 0), 2);
        $unsubscribeUrl = config('viu.unsubscribe_url')
            ?: 'mailto:'.(config('mail.from.address') ?: 'support@fullviu.com');

        try {
            Mail::to($waitlist->email)->send(new InquiryAcknowledgmentMail(
                firstName: $firstName,
                zipCode: (string) $waitlist->zip_code,
                amount: $amount,
                checkoutUrl: (string) $waitlist->checkout_url,
                unsubscribeUrl: $unsubscribeUrl,
            ));
        } catch (Throwable $exception) {
            Log::error('Failed to send waitlist checkout link email.', [
                'waitlist_id' => $waitlist->id,
                'recipient' => $waitlist->email,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
