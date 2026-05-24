<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_settings') || ! Schema::hasTable('settings')) {
            return;
        }

        $emailSettings = DB::table('email_settings')->first();

        if ($emailSettings === null) {
            return;
        }

        $legacyEnabled = DB::table('settings')
            ->where('key', 'email.admin_notification_enabled')
            ->value('value');

        $legacyAddress = DB::table('settings')
            ->where('key', 'email.admin_notification_address')
            ->value('value');

        $updates = [];

        if ($legacyEnabled !== null) {
            $updates['admin_notification_enabled'] = filter_var($legacyEnabled, FILTER_VALIDATE_BOOLEAN);
        }

        if (filled($legacyAddress)) {
            $updates['admin_notification_address'] = $legacyAddress;
        }

        if ($updates !== []) {
            DB::table('email_settings')
                ->where('id', $emailSettings->id)
                ->update($updates);
        }
    }

    public function down(): void
    {
        //
    }
};
