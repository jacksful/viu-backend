<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->text('meta_keywords')->nullable()->after('seo_description');
            $table->string('canonical_url', 2048)->nullable()->after('meta_keywords');
            $table->string('og_title')->nullable()->after('canonical_url');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image_path')->nullable()->after('og_description');
            $table->string('twitter_title')->nullable()->after('og_image_path');
            $table->text('twitter_description')->nullable()->after('twitter_title');
            $table->string('twitter_image_path')->nullable()->after('twitter_description');
            $table->string('robots')->default('index,follow')->after('twitter_image_path');
            $table->json('meta_tags')->nullable()->after('robots');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'meta_keywords',
                'canonical_url',
                'og_title',
                'og_description',
                'og_image_path',
                'twitter_title',
                'twitter_description',
                'twitter_image_path',
                'robots',
                'meta_tags',
            ]);
        });
    }
};
