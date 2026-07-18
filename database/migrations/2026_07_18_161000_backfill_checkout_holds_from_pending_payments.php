<?php

use App\Models\CheckoutHold;
use App\Models\StripePayment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('checkout_holds')) {
            return;
        }

        StripePayment::query()
            ->where('status', 'checkout_pending')
            ->whereDoesntHave('checkoutHold')
            ->orderBy('id')
            ->each(function (StripePayment $payment): void {
                CheckoutHold::create([
                    'stripe_payment_id' => $payment->id,
                    'zipcode_id' => $payment->zipcode_id,
                    'waitlist_id' => filled($payment->metadata['waitlist_id'] ?? null)
                        ? (int) $payment->metadata['waitlist_id']
                        : null,
                    'status' => CheckoutHold::STATUS_ACTIVE,
                    'checkout_started_at' => $payment->created_at ?? now(),
                    'hold_expires_at' => ($payment->created_at ?? now())->addDays(CheckoutHold::HOLD_DAYS),
                ]);
            });
    }

    public function down(): void
    {
        //
    }
};
