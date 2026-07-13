<?php

namespace App\Jobs;

use App\Mail\InquiryAcknowledgmentMail;
use App\Models\Contact;
use App\Models\EmailSetting;
use App\Models\UserZipcodeSubscription;
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
        public int $contactId,
        public int $subscriptionId,
        public int $zipcodeId,
    ) {}

    public function uniqueId(): string
    {
        return 'zip-available-inquiry:'.$this->contactId.':'.$this->subscriptionId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $contact = Contact::query()->find($this->contactId);
        $subscription = UserZipcodeSubscription::query()->find($this->subscriptionId);
        $zipcode = Zipcode::query()->find($this->zipcodeId);

        if (! $contact || blank($contact->email) || blank($contact->zip_of_interest)) {
            return;
        }

        if (! $subscription || ! in_array($subscription->status, ['canceled', 'expired'], true)) {
            return;
        }

        if (! $zipcode || trim((string) $contact->zip_of_interest) !== trim((string) $zipcode->code)) {
            return;
        }

        if ($contact->zip_available_notice_sent_for_subscription_id === $subscription->id) {
            return;
        }

        if (UserZipcodeSubscription::active()->forZipcode($zipcode->id)->exists()) {
            return;
        }

        $firstName = filled($contact->name)
            ? (explode(' ', trim($contact->name), 2)[0] ?: 'there')
            : 'there';

        $amount = '$'.number_format((float) ($zipcode->monthly_price ?? 0), 2);
        $checkoutUrl = URL::route('home').'#pricing';
        $unsubscribeUrl = config('viu.unsubscribe_url')
            ?: 'mailto:'.(config('mail.from.address') ?: 'support@fullviu.com');

        try {
            Mail::to($contact->email)->send(new InquiryAcknowledgmentMail(
                firstName: $firstName,
                zipCode: (string) $zipcode->code,
                amount: $amount,
                checkoutUrl: $checkoutUrl,
                unsubscribeUrl: $unsubscribeUrl,
            ));

            $contact->update([
                'zip_available_notice_sent_for_subscription_id' => $subscription->id,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send ZIP available inquiry email.', [
                'contact_id' => $contact->id,
                'subscription_id' => $subscription->id,
                'zipcode_id' => $zipcode->id,
                'recipient' => $contact->email,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
