<?php

namespace App\Jobs;

use App\Mail\WaitlistConfirmationMail;
use App\Models\Contact;
use App\Models\EmailSetting;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWaitlistConfirmationEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $contactId,
    ) {}

    public function uniqueId(): string
    {
        return 'waitlist-confirmation:'.$this->contactId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $contact = Contact::query()->find($this->contactId);

        if (! $contact || blank($contact->email) || blank($contact->zip_of_interest)) {
            return;
        }

        $zipCode = trim((string) $contact->zip_of_interest);
        $firstName = filled($contact->name)
            ? (explode(' ', trim($contact->name), 2)[0] ?: 'there')
            : 'there';

        $waitlistPosition = Contact::query()
            ->where('zip_of_interest', $zipCode)
            ->where('id', '<=', $contact->id)
            ->count();

        $territoryStatus = $this->resolveTerritoryStatus($zipCode);
        $unsubscribeUrl = config('viu.unsubscribe_url')
            ?: 'mailto:'.(config('mail.from.address') ?: 'support@fullviu.com');

        try {
            Mail::to($contact->email)->send(new WaitlistConfirmationMail(
                firstName: $firstName,
                zipCode: $zipCode,
                territoryStatus: $territoryStatus,
                waitlistPosition: max(1, $waitlistPosition),
                unsubscribeUrl: $unsubscribeUrl,
            ));
        } catch (Throwable $exception) {
            Log::error('Failed to send waitlist confirmation email.', [
                'contact_id' => $contact->id,
                'recipient' => $contact->email,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function resolveTerritoryStatus(string $zipCode): string
    {
        $zipcode = Zipcode::query()
            ->where('code', $zipCode)
            ->first();

        if (! $zipcode) {
            return 'On hold';
        }

        $isClaimed = UserZipcodeSubscription::active()
            ->forZipcode($zipcode->id)
            ->exists();

        return $isClaimed ? 'Claimed' : 'On hold';
    }
}
