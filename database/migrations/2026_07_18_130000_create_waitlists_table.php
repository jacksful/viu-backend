<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('zip_code', 20);
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'read', 'contacted', 'archived'])->default('new');
            $table->unsignedBigInteger('zip_available_notice_sent_for_subscription_id')->nullable();
            $table->timestamps();

            $table->index('zip_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlists');
    }
};
