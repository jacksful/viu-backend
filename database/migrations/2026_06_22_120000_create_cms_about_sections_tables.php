<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_about_hero_sections', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text');
            $table->string('title');
            $table->text('lead')->nullable();
            $table->timestamps();
        });

        Schema::create('cms_about_mission_sections', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text');
            $table->string('headline');
            $table->text('intro_text')->nullable();
            $table->text('body_middle')->nullable();
            $table->text('body_last')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('cms_about_principles_sections', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text');
            $table->string('heading');
            $table->json('principles')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_about_principles_sections');
        Schema::dropIfExists('cms_about_mission_sections');
        Schema::dropIfExists('cms_about_hero_sections');
    }
};
