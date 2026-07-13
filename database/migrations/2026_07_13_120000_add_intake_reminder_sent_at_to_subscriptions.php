<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->timestamp('intake_reminder_sent_at')->nullable()->after('expiration_email_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->dropColumn('intake_reminder_sent_at');
        });
    }
};
