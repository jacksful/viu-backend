<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pricing_sections', function (Blueprint $table) {
            $table->id();
            $table->string('left_image_path')->nullable();
            $table->string('card_label_starting');
            $table->string('card_price_display');
            $table->string('card_price_period');
            $table->string('card_per_label');
            $table->text('card_footer_note')->nullable();
            $table->string('badge_text');
            $table->string('heading');
            $table->text('intro')->nullable();
            $table->json('feature_lines')->nullable();
            $table->string('cta_label');
            $table->string('cta_href', 2048);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pricing_sections');
    }
};
