<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_about_hero_sections', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('lead');
        });
    }

    public function down(): void
    {
        Schema::table('cms_about_hero_sections', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
