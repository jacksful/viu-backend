<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->enum('billing_interval', ['month', 'year'])
                ->nullable()
                ->after('stripe_customer_id');
        });

        Schema::table('stripe_payments', function (Blueprint $table) {
            $table->enum('billing_interval', ['month', 'year'])
                ->nullable()
                ->after('billing_reason');
        });
    }

    public function down(): void
    {
        Schema::table('stripe_payments', function (Blueprint $table) {
            $table->dropColumn('billing_interval');
        });

        Schema::table('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->dropColumn('billing_interval');
        });
    }
};
