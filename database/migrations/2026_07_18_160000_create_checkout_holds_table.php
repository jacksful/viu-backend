<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stripe_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zipcode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('waitlist_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('checkout_started_at');
            $table->timestamp('hold_expires_at');
            $table->timestamp('released_at')->nullable();
            $table->string('release_reason')->nullable();
            $table->timestamp('recovery_email_sent_at')->nullable();
            $table->string('recovery_email_status')->nullable();
            $table->text('recovery_email_error')->nullable();
            $table->timestamps();

            $table->index(['zipcode_id', 'status', 'hold_expires_at']);
            $table->index(['status', 'hold_expires_at']);
        });

        Schema::table('waitlists', function (Blueprint $table) {
            $table->foreignId('zip_available_notice_sent_for_hold_id')
                ->nullable()
                ->after('zip_available_notice_sent_for_subscription_id')
                ->constrained('checkout_holds')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('waitlists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('zip_available_notice_sent_for_hold_id');
        });

        Schema::dropIfExists('checkout_holds');
    }
};
