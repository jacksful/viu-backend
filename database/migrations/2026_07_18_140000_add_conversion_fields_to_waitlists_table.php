<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waitlists', function (Blueprint $table) {
            $table->foreignId('converted_to_user_id')
                ->nullable()
                ->after('zip_available_notice_sent_for_subscription_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('converted_at')->nullable()->after('converted_to_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('waitlists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('converted_to_user_id');
            $table->dropColumn('converted_at');
        });
    }
};
