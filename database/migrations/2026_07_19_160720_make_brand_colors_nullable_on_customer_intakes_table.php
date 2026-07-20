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
        Schema::table('customer_intakes', function (Blueprint $table) {
            $table->string('brand_color_1', 7)->nullable()->change();
            $table->string('brand_color_2', 7)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_intakes', function (Blueprint $table) {
            $table->string('brand_color_1', 7)->nullable(false)->change();
            $table->string('brand_color_2', 7)->nullable(false)->change();
        });
    }
};
