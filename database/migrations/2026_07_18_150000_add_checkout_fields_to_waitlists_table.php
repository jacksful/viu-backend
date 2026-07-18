<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waitlists', function (Blueprint $table) {
            $table->foreignId('stripe_payment_id')
                ->nullable()
                ->after('converted_at')
                ->constrained('stripe_payments')
                ->nullOnDelete();
            $table->string('checkout_url')->nullable()->after('stripe_payment_id');
            $table->timestamp('locked_until')->nullable()->after('checkout_url');

            $table->index('locked_until');
        });
    }

    public function down(): void
    {
        Schema::table('waitlists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stripe_payment_id');
            $table->dropColumn(['checkout_url', 'locked_until']);
        });
    }
};
