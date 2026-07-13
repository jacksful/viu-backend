<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('zip_available_notice_sent_for_subscription_id')
                ->nullable()
                ->after('status')
                ->constrained('user_zipcode_subscriptions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('zip_available_notice_sent_for_subscription_id');
        });
    }
};
