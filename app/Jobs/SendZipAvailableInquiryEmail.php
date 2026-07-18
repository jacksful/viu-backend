<?php

namespace App\Jobs;

use App\Mail\InquiryAcknowledgmentMail;
use App\Models\EmailSetting;
use App\Models\UserZipcodeSubscription;
use App\Models\Waitlist;
use App\Models\Zipcode;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendZipAvailableInquiryEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $waitlistId,
        public int $subscriptionId,
        public int $zipcodeId,
    ) {}

    public function uniqueId(): string
    {
        return 'zip-available-inquiry:'.$this->waitlistId.':'.$this->subscriptionId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $waitlist = Waitlist::query()->find($this->waitlistId);
        $subscription = UserZipcodeSubscription::query()->find($this->subscriptionId);
        $zipcode = Zipcode::query()->find($this->zipcodeId);

        if (! $waitlist || blank($waitlist->email) || blank($waitlist->zip_code)) {
            return;
        }

        if (! $subscription || ! in_array($subscription->status, ['canceled', 'expired'], true)) {
            return;
        }

        if (! $zipcode || trim((string) $waitlist->zip_code) !== trim((string) $zipcode->code)) {
            return;
        }

        if ($waitlist->zip_available_notice_sent_for_subscription_id === $subscription->id) {
            return;
        }

        if (UserZipcodeSubscription::active()->forZipcode($zipcode->id)->exists()) {
            return;
        }

        $firstName = filled($waitlist->name)
            ? (explode(' ', trim($waitlist->name), 2)[0] ?: 'there')
            : 'there';

        $amount = '$'.number_format((float) ($zipcode->monthly_price ?? 0), 2);
        $checkoutUrl = URL::route('home').'#pricing';
        $unsubscribeUrl = config('viu.unsubscribe_url')
            ?: 'mailto:'.(config('mail.from.address') ?: 'support@fullviu.com');

        try {
            Mail::to($waitlist->email)->send(new InquiryAcknowledgmentMail(
                firstName: $firstName,
                zipCode: (string) $zipcode->code,
                amount: $amount,
                checkoutUrl: $checkoutUrl,
                unsubscribeUrl: $unsubscribeUrl,
            ));

            $waitlist->update([
                'zip_available_notice_sent_for_subscription_id' => $subscription->id,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send ZIP available inquiry email.', [
                'waitlist_id' => $waitlist->id,
                'subscription_id' => $subscription->id,
                'zipcode_id' => $zipcode->id,
                'recipient' => $waitlist->email,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
