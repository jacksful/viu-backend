<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('menu_label')->nullable()->after('is_homepage');
            $table->unsignedInteger('menu_sort_order')->default(0)->after('menu_label');
            $table->json('menu_positions')->nullable()->after('menu_sort_order');
            $table->string('body_class')->nullable()->after('menu_positions');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['menu_label', 'menu_sort_order', 'menu_positions', 'body_class']);
        });
    }
};
