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
        Schema::create('user_zipcode_subscriptions', function (Blueprint $table) {
            $table->id();

            // only customers should subscribe
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Store multiple zipcode IDs as JSON array
            $table->json('zipcode_ids')->nullable();

            // subscription details
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // active, expired, canceled, pending
            $table->enum('status', ['pending', 'active', 'expired', 'canceled'])
                ->default('pending');

            $table->timestamps();

            // Remove unique constraint since we're storing multiple zipcodes per subscription
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_zipcode_subscriptions');
    }
};
