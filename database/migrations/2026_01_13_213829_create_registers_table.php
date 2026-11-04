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
        Schema::create('registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('opening_time')->nullable();
            $table->dateTime('closing_time')->nullable();
            $table->decimal('opening_amount', 15, 2)->nullable();
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('total_refund', 15, 2)->default(0);
            $table->decimal('total_payment', 15, 2)->default(0); // Total received
            $table->decimal('total_expense', 15, 2)->default(0);
            $table->decimal('credit_sales', 15, 2)->default(0);
            $table->decimal('cash_in_hand', 15, 2)->default(0); // Calculated cash
            $table->text('closing_note')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registers');
    }
};
