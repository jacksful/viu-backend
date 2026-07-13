<?php

namespace App\Console\Commands;

use App\Jobs\SendCardExpiringEmail;
use App\Models\UserZipcodeSubscription;
use Illuminate\Console\Command;

class SendSubscriptionCardExpiringNoticesCommand extends Command
{
    protected $signature = 'subscriptions:send-card-expiring-notices {--days=30 : Days before card expiry to send the notice}';

    protected $description = 'Send card expiring notices for active subscriptions with saved Stripe cards nearing expiration';

    public function handle(): int
    {
        $daysBeforeExpiry = max(1, (int) $this->option('days'));

        $subscriptions = UserZipcodeSubscription::query()
            ->active()
            ->where('cancel_at_period_end', false)
            ->whereNotNull('stripe_customer_id')
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No active subscriptions to check for expiring cards.');

            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            SendCardExpiringEmail::dispatch($subscription->id, $daysBeforeExpiry);
        }

        $this->info("Queued {$subscriptions->count()} card expiring notice check(s).");

        return self::SUCCESS;
    }
}
