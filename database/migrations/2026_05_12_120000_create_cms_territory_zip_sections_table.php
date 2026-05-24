<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_territory_zip_sections', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text');
            $table->string('headline_primary');
            $table->string('headline_accent');
            $table->text('intro')->nullable();
            $table->json('checklist_items')->nullable();
            $table->json('feature_columns')->nullable();
            $table->string('left_visual_image_path')->nullable();
            $table->string('left_card_icon_path')->nullable();
            $table->string('card_kicker');
            $table->string('card_title');
            $table->string('checklist_check_icon_path')->nullable();
            $table->string('quote_icon_path')->nullable();
            $table->text('quote_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_territory_zip_sections');
    }
};
