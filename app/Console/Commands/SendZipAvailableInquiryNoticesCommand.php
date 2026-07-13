<?php

namespace App\Console\Commands;

use App\Jobs\SendZipAvailableInquiryEmail;
use App\Models\Contact;
use App\Models\UserZipcodeSubscription;
use Illuminate\Console\Command;

class SendZipAvailableInquiryNoticesCommand extends Command
{
    protected $signature = 'subscriptions:send-zip-available-inquiry-notices {--days=3 : Days after subscription end to notify interested contacts}';

    protected $description = 'Notify waitlisted contacts when a ZIP becomes available after a subscription expires';

    public function handle(): int
    {
        $daysAfterEnd = max(1, (int) $this->option('days'));
        $targetDate = now()->subDays($daysAfterEnd)->toDateString();

        $subscriptions = UserZipcodeSubscription::query()
            ->whereIn('status', ['canceled', 'expired'])
            ->whereNotNull('end_date')
            ->whereDate('end_date', $targetDate)
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No expired subscriptions due for ZIP availability inquiry emails.');

            return self::SUCCESS;
        }

        $queued = 0;

        foreach ($subscriptions as $subscription) {
            $zipcodes = $subscription->zipcodes;

            if ($zipcodes->isEmpty()) {
                continue;
            }

            foreach ($zipcodes as $zipcode) {
                if (UserZipcodeSubscription::active()->forZipcode($zipcode->id)->exists()) {
                    continue;
                }

                $contacts = Contact::query()
                    ->where('zip_of_interest', $zipcode->code)
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->where(function ($query) use ($subscription) {
                        $query->whereNull('zip_available_notice_sent_for_subscription_id')
                            ->orWhere('zip_available_notice_sent_for_subscription_id', '!=', $subscription->id);
                    })
                    ->orderBy('id')
                    ->get();

                foreach ($contacts as $contact) {
                    SendZipAvailableInquiryEmail::dispatch(
                        $contact->id,
                        $subscription->id,
                        $zipcode->id,
                    );

                    $queued++;
                }
            }
        }

        if ($queued === 0) {
            $this->info('No interested contacts to notify for recently expired subscriptions.');

            return self::SUCCESS;
        }

        $this->info("Queued {$queued} ZIP availability inquiry email(s).");

        return self::SUCCESS;
    }
}
