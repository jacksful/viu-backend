<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminderEmail;
use App\Models\StripePayment;
use App\Models\UserZipcodeSubscription;
use Illuminate\Console\Command;

class SendSubscriptionPaymentRemindersCommand extends Command
{
    protected $signature = 'subscriptions:send-payment-reminders {--hours=24 : Hours before subscription end to send the payment reminder}';

    protected $description = 'Send payment reminder emails for subscriptions expiring within the next 24 hours';

    public function handle(): int
    {
        $hoursBeforeEnd = max(1, (int) $this->option('hours'));
        $targetDate = now()->addHours($hoursBeforeEnd)->toDateString();

        $subscriptions = UserZipcodeSubscription::query()
            ->where('cancel_at_period_end', false)
            ->whereNotNull('end_date')
            ->whereDate('end_date', $targetDate)
            ->whereIn('status', ['active', 'expired'])
            ->where(function ($query) {
                $query->whereNull('payment_reminder_sent_for_end_date')
                    ->orWhereColumn('payment_reminder_sent_for_end_date', '!=', 'end_date');
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
            $this->info('No subscriptions due for payment reminder emails.');

            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            SendPaymentReminderEmail::dispatch($subscription->id);
        }

        $this->info("Queued {$subscriptions->count()} payment reminder email(s).");

        return self::SUCCESS;
    }
}
