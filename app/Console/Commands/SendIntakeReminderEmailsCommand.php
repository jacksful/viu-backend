<?php

namespace App\Console\Commands;

use App\Jobs\SendIntakeReminderEmail;
use App\Models\UserZipcodeSubscription;
use Illuminate\Console\Command;

class SendIntakeReminderEmailsCommand extends Command
{
    protected $signature = 'subscriptions:send-intake-reminders {--days=10 : Days after subscription start to send the reminder}';

    protected $description = 'Send intake reminder emails for active subscriptions with incomplete intake forms';

    public function handle(): int
    {
        $daysAfterStart = max(1, (int) $this->option('days'));
        $cutoffDate = now()->subDays($daysAfterStart)->toDateString();

        $subscriptions = UserZipcodeSubscription::query()
            ->active()
            ->whereNotNull('start_date')
            ->whereDate('start_date', '<=', $cutoffDate)
            ->whereNull('intake_reminder_sent_at')
            ->where(function ($query) {
                $query->whereDoesntHave('customerIntake')
                    ->orWhereHas('customerIntake', function ($intakeQuery) {
                        $intakeQuery->whereNull('submitted_at');
                    });
            })
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions due for intake reminders.');

            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            SendIntakeReminderEmail::dispatch($subscription->id);
        }

        $this->info("Queued {$subscriptions->count()} intake reminder email(s).");

        return self::SUCCESS;
    }
}
