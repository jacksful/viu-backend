<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_qa_sections', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text');
            $table->string('heading');
            $table->text('intro')->nullable();
            $table->string('support_label');
            $table->string('support_email');
            $table->string('support_icon_path')->nullable();
            $table->json('faq_items')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_qa_sections');
    }
};
