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
        // Schema::dropIfExists('delivery_orders');
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('counter_id');
            $table->unsignedBigInteger('rider_id')->nullable();
            $table->string('status')->default('None'); // Preparing, Transit, Delivered, Canceled, Return
            $table->timestamp('preparing_at')->nullable();
            $table->integer('preparing_due_at')->nullable();
            $table->timestamp('transit_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->integer('delivery_due_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
