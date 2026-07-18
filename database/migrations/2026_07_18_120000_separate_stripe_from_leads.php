<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stripe_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_id');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'stripe_checkout_session_id',
                'stripe_subscription_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid')->after('lead_status');
            $table->string('stripe_checkout_session_id')->nullable()->index()->after('payment_status');
            $table->string('stripe_subscription_id')->nullable()->index()->after('stripe_checkout_session_id');
        });

        Schema::table('stripe_payments', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }
};
