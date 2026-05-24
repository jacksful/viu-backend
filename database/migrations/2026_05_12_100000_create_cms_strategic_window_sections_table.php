<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_strategic_window_sections', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text');
            $table->string('headline_primary');
            $table->string('headline_accent');
            $table->text('intro')->nullable();
            $table->json('features')->nullable();
            $table->string('visual_image_path')->nullable();
            $table->string('card_icon_path')->nullable();
            $table->string('card_kicker')->nullable();
            $table->string('card_title')->nullable();
            $table->string('card_metric_label')->nullable();
            $table->unsignedTinyInteger('card_metric_percent')->default(42);
            $table->text('card_quote')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_strategic_window_sections');
    }
};
