<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('uploaded_zipcode_id')->nullable();
            $table->foreign('uploaded_zipcode_id')->references('id')->on('uploaded_zipcodes')->onDelete('set null');

            $table->string('propertyid')->nullable();
            $table->string('restype')->nullable();
            $table->string('tax_value')->nullable();
            $table->string('address')->nullable();
            $table->string('times_sold')->nullable();
            $table->string('day_since_sold')->nullable();
            $table->string('last_date_sold')->nullable();
            $table->string('township')->nullable();
            $table->string('style')->nullable();
            $table->string('yearbuilt')->nullable();
            $table->string('extwallfinish_desc')->nullable();
            $table->string('rooftype_desc')->nullable();
            $table->string('roofmaterial_desc')->nullable();
            $table->string('basement_desc')->nullable();
            $table->string('hctype')->nullable();
            $table->string('hcfueltype_desc')->nullable();
            $table->string('hcsystemtype_desc')->nullable();
            $table->string('bedrooms')->nullable();
            $table->string('fullbaths')->nullable();
            $table->string('sfla')->nullable();
            $table->string('phycondition')->nullable();
            $table->string('utility')->nullable();
            $table->string('propdesirability')->nullable();
            $table->string('locdesirability')->nullable();
            $table->string('status')->nullable();
            $table->string('predicted_status')->nullable();
            $table->string('correct_status')->nullable();
            $table->string('status_probability')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};
