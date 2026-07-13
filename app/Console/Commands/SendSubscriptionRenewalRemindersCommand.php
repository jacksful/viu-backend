<?php

namespace App\Console\Commands;

use App\Jobs\SendRenewalReminderEmail;
use App\Models\UserZipcodeSubscription;
use Illuminate\Console\Command;

class SendSubscriptionRenewalRemindersCommand extends Command
{
    protected $signature = 'subscriptions:send-renewal-reminders {--days=10 : Days before renewal to send the reminder}';

    protected $description = 'Send renewal reminder emails for active subscriptions nearing their end date';

    public function handle(): int
    {
        $daysBeforeRenewal = max(1, (int) $this->option('days'));
        $targetDate = now()->addDays($daysBeforeRenewal)->toDateString();

        $subscriptions = UserZipcodeSubscription::query()
            ->active()
            ->where('cancel_at_period_end', false)
            ->whereNotNull('end_date')
            ->whereDate('end_date', $targetDate)
            ->where(function ($query) {
                $query->whereNull('renewal_reminder_sent_for_end_date')
                    ->orWhereColumn('renewal_reminder_sent_for_end_date', '!=', 'end_date');
            })
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions due for renewal reminders.');

            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            SendRenewalReminderEmail::dispatch($subscription->id);
        }

        $this->info("Queued {$subscriptions->count()} renewal reminder email(s).");

        return self::SUCCESS;
    }
}
