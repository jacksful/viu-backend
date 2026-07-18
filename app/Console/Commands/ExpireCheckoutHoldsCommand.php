<?php

namespace App\Console\Commands;

use App\Services\CheckoutHoldService;
use Illuminate\Console\Command;

class ExpireCheckoutHoldsCommand extends Command
{
    protected $signature = 'checkout-holds:expire';

    protected $description = 'Expire checkout holds past their deadline and notify waitlisted users';

    public function handle(CheckoutHoldService $holdService): int
    {
        $expiredCount = $holdService->expireDueHolds();

        if ($expiredCount === 0) {
            $this->info('No checkout holds to expire.');

            return self::SUCCESS;
        }

        $this->info("Expired {$expiredCount} checkout hold(s) and queued waitlist notifications.");

        return self::SUCCESS;
    }
}
