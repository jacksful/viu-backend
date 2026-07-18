<?php

namespace App\Jobs;

use App\Mail\WaitlistConfirmationMail;
use App\Models\EmailSetting;
use App\Models\UserZipcodeSubscription;
use App\Models\Waitlist;
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
        public int $waitlistId,
    ) {}

    public function uniqueId(): string
    {
        return 'waitlist-confirmation:'.$this->waitlistId;
    }

    public function handle(): void
    {
        EmailSetting::applyMailConfig();

        $waitlist = Waitlist::query()->find($this->waitlistId);

        if (! $waitlist || blank($waitlist->email) || blank($waitlist->zip_code)) {
            return;
        }

        $zipCode = trim((string) $waitlist->zip_code);
        $firstName = filled($waitlist->name)
            ? (explode(' ', trim($waitlist->name), 2)[0] ?: 'there')
            : 'there';

        $waitlistPosition = Waitlist::query()
            ->where('zip_code', $zipCode)
            ->where('id', '<=', $waitlist->id)
            ->count();

        $territoryStatus = $this->resolveTerritoryStatus($zipCode);
        $unsubscribeUrl = config('viu.unsubscribe_url')
            ?: 'mailto:'.(config('mail.from.address') ?: 'support@fullviu.com');

        try {
            Mail::to($waitlist->email)->send(new WaitlistConfirmationMail(
                firstName: $firstName,
                zipCode: $zipCode,
                territoryStatus: $territoryStatus,
                waitlistPosition: max(1, $waitlistPosition),
                unsubscribeUrl: $unsubscribeUrl,
            ));
        } catch (Throwable $exception) {
            Log::error('Failed to send waitlist confirmation email.', [
                'waitlist_id' => $waitlist->id,
                'recipient' => $waitlist->email,
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
