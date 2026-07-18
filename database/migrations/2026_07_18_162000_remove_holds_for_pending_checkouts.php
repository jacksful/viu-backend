<?php

use App\Models\CheckoutHold;
use App\Models\StripePayment;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        CheckoutHold::query()
            ->whereHas('stripePayment', fn ($query) => $query->where('status', 'checkout_pending'))
            ->delete();
    }

    public function down(): void
    {
        //
    }
};
