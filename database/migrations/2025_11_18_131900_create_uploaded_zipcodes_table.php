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
        Schema::create('uploaded_zipcodes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('zipcode_id')->nullable();
            $table->foreign('zipcode_id')->references('id')->on('zipcodes')->onDelete('set null');

            // Month and Year input fields
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('csv_file')->nullable();
            $table->string('status')->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_zipcodes');
    }
};
