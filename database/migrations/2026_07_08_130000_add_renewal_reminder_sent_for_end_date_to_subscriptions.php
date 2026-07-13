<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->date('renewal_reminder_sent_for_end_date')->nullable()->after('cancel_at_period_end');
        });
    }

    public function down(): void
    {
        Schema::table('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->dropColumn('renewal_reminder_sent_for_end_date');
        });
    }
};
