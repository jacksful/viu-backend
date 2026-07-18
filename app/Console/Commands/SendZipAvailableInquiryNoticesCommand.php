<?php

namespace App\Console\Commands;

use App\Jobs\SendZipAvailableInquiryEmail;
use App\Models\UserZipcodeSubscription;
use App\Models\Waitlist;
use Illuminate\Console\Command;

class SendZipAvailableInquiryNoticesCommand extends Command
{
    protected $signature = 'subscriptions:send-zip-available-inquiry-notices {--days=3 : Days after subscription end to notify waitlisted users}';

    protected $description = 'Notify waitlisted users when a ZIP becomes available after a subscription expires';

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

                $waitlistEntries = Waitlist::query()
                    ->where('zip_code', $zipcode->code)
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->where(function ($query) use ($subscription) {
                        $query->whereNull('zip_available_notice_sent_for_subscription_id')
                            ->orWhere('zip_available_notice_sent_for_subscription_id', '!=', $subscription->id);
                    })
                    ->orderBy('id')
                    ->get();

                foreach ($waitlistEntries as $waitlist) {
                    SendZipAvailableInquiryEmail::dispatch(
                        $waitlist->id,
                        $subscription->id,
                        $zipcode->id,
                    );

                    $queued++;
                }
            }
        }

        if ($queued === 0) {
            $this->info('No waitlist entries to notify for recently expired subscriptions.');

            return self::SUCCESS;
        }

        $this->info("Queued {$queued} ZIP availability inquiry email(s).");

        return self::SUCCESS;
    }
}
