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
        Schema::create('pcounters', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('cust_id');
            $table->date('day');
            $table->integer('bilty_no')->nullable();
            $table->string('ref')->nullable();
            $table->integer('gross')->nullable();
            $table->integer('InvDiscount')->nullable();
            $table->integer('InvProfit')->nullable();
            $table->integer('received');
            $table->integer('remaining');
            $table->integer('finalVal');
            $table->integer('tax')->nullable();
            $table->bigInteger('original_purchase_id')->nullable();
            $table->string('inv_status')->default('Original');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pcounters');
    }
};
