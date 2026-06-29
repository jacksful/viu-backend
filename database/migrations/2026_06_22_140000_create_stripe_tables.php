<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->boolean('test_mode')->default(true);
            $table->text('publishable_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->string('currency', 3)->default('usd');
            $table->string('success_url')->nullable();
            $table->string('cancel_url')->nullable();
            $table->timestamps();
        });

        Schema::create('stripe_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_zipcode_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('zipcode_id')->nullable();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_invoice_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->unsignedInteger('amount_cents')->default(0);
            $table->string('currency', 3)->default('usd');
            $table->string('status')->default('pending');
            $table->string('billing_reason')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('zipcode_id')->references('id')->on('zipcodes')->nullOnDelete();
        });

        Schema::create('stripe_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_event_id')->unique();
            $table->string('type');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->index()->after('profile_photo_path');
            $table->string('pm_type')->nullable()->after('stripe_id');
            $table->string('pm_last_four', 4)->nullable()->after('pm_type');
        });

        Schema::table('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->index()->after('status');
            $table->string('stripe_customer_id')->nullable()->index()->after('stripe_subscription_id');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->index()->after('payment_status');
            $table->string('stripe_subscription_id')->nullable()->index()->after('stripe_checkout_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['stripe_checkout_session_id', 'stripe_subscription_id']);
        });

        Schema::table('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['stripe_subscription_id', 'stripe_customer_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_id', 'pm_type', 'pm_last_four']);
        });

        Schema::dropIfExists('stripe_webhook_events');
        Schema::dropIfExists('stripe_payments');
        Schema::dropIfExists('stripe_settings');
    }
};
