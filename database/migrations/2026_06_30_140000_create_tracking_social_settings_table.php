<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_social_settings', function (Blueprint $table) {
            $table->id();

            $table->string('google_analytics_measurement_id')->nullable();
            $table->string('google_tag_manager_id')->nullable();
            $table->string('google_search_console_verification')->nullable();
            $table->boolean('google_analytics_enabled')->default(false);
            $table->boolean('google_tag_manager_enabled')->default(false);

            $table->string('facebook_pixel_id')->nullable();
            $table->string('facebook_domain_verification')->nullable();
            $table->boolean('facebook_pixel_enabled')->default(false);

            $table->string('tiktok_pixel_id')->nullable();
            $table->string('linkedin_insight_tag_id')->nullable();
            $table->string('pinterest_tag_id')->nullable();
            $table->string('twitter_pixel_id')->nullable();
            $table->string('snapchat_pixel_id')->nullable();
            $table->boolean('tiktok_pixel_enabled')->default(false);
            $table->boolean('linkedin_insight_enabled')->default(false);
            $table->boolean('pinterest_tag_enabled')->default(false);
            $table->boolean('twitter_pixel_enabled')->default(false);
            $table->boolean('snapchat_pixel_enabled')->default(false);

            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('pinterest_url')->nullable();
            $table->string('whatsapp_number')->nullable();

            $table->string('default_meta_title')->nullable();
            $table->text('default_meta_description')->nullable();
            $table->text('default_meta_keywords')->nullable();
            $table->string('default_og_image_path')->nullable();
            $table->string('default_robots')->default('index,follow');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_social_settings');
    }
};
