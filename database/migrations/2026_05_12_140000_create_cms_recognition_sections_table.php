<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_recognition_sections', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text');
            $table->string('headline_line_1');
            $table->string('headline_line_2');
            $table->string('headline_line_3');
            $table->text('intro')->nullable();
            $table->string('box_top_left');
            $table->string('box_top_right');
            $table->text('box_wide_body');
            $table->string('box_wide_accent');
            $table->string('right_image_path')->nullable();
            $table->string('card_logo_path')->nullable();
            $table->string('card_kicker');
            $table->string('card_title');
            $table->string('card_progress_label_left');
            $table->string('card_progress_label_right');
            $table->unsignedTinyInteger('card_progress_percent')->default(95);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_recognition_sections');
    }
};
