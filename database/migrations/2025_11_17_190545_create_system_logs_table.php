<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->index();
            $table->dateTime('date')->index();
            $table->text('message');
            $table->text('context')->nullable();
            $table->text('stack')->nullable();
            $table->string('file', 255)->index()->default('laravel.log');
            $table->timestamps();
            
            // Composite index for common queries
            $table->index(['level', 'date']);
            $table->index(['file', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};