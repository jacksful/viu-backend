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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('initial_notes')->nullable();
            $table->enum('lead_status', ['new', 'interested', 'contacted', 'not_interested'])->default('new');
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');
            $table->date('last_contact_date')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->text('internal_comments')->nullable();
            $table->foreignId('converted_to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
