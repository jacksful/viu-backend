<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_intakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_zipcode_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zipcode_id')->constrained()->cascadeOnDelete();
            $table->string('headshot_path')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('brokerage_logo_path')->nullable();
            $table->string('lifestyle_photo_path')->nullable();
            $table->string('brand_color_1', 7);
            $table->string('brand_color_2', 7);
            $table->string('full_name');
            $table->string('tagline');
            $table->text('bio');
            $table->unsignedTinyInteger('years_in_business');
            $table->string('credential');
            $table->string('display_phone');
            $table->string('display_email');
            $table->string('website_url');
            $table->string('instagram')->nullable();
            $table->string('booking_url')->nullable();
            $table->string('brokerage_name');
            $table->string('brokerage_address');
            $table->string('license_number');
            $table->string('license_state', 2);
            $table->text('disclaimers')->nullable();
            $table->boolean('equal_housing_acknowledged')->default(false);
            $table->boolean('confirmed')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique('user_zipcode_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_intakes');
    }
};
