<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zipcodes', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable()->after('yearly_price');
            $table->string('stripe_price_id')->nullable()->after('stripe_product_id');
            $table->string('stripe_monthly_price_id')->nullable()->after('stripe_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('zipcodes', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_product_id',
                'stripe_price_id',
                'stripe_monthly_price_id',
            ]);
        });
    }
};
