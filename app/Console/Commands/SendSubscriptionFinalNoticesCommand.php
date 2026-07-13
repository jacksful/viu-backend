<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentFinalNoticeEmail;
use App\Models\StripePayment;
use App\Models\UserZipcodeSubscription;
use Illuminate\Console\Command;

class SendSubscriptionFinalNoticesCommand extends Command
{
    protected $signature = 'subscriptions:send-final-notices {--days=3 : Days before subscription end to send the final notice}';

    protected $description = 'Send payment final notice emails for subscriptions at risk of expiring';

    public function handle(): int
    {
        $daysBeforeEnd = max(1, (int) $this->option('days'));
        $targetDate = now()->addDays($daysBeforeEnd)->toDateString();

        $subscriptions = UserZipcodeSubscription::query()
            ->where('cancel_at_period_end', false)
            ->whereNotNull('end_date')
            ->whereDate('end_date', $targetDate)
            ->whereIn('status', ['active', 'expired'])
            ->where(function ($query) {
                $query->whereNull('final_notice_sent_for_end_date')
                    ->orWhereColumn('final_notice_sent_for_end_date', '!=', 'end_date');
            })
            ->where(function ($query) {
                $query->where('status', 'expired')
                    ->orWhereIn('id', StripePayment::query()
                        ->select('user_zipcode_subscription_id')
                        ->where('status', 'failed')
                        ->whereNotNull('user_zipcode_subscription_id'));
            })
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions due for final notice emails.');

            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            SendPaymentFinalNoticeEmail::dispatch($subscription->id);
        }

        $this->info("Queued {$subscriptions->count()} final notice email(s).");

        return self::SUCCESS;
    }
}
