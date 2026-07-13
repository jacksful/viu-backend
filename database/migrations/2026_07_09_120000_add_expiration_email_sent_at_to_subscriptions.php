<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->timestamp('expiration_email_sent_at')->nullable()->after('cancellation_confirmation_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->dropColumn('expiration_email_sent_at');
        });
    }
};
