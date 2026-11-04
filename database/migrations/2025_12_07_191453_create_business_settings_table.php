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
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            $table->string('business_name')->nullable();
            $table->string('business_type')->nullable();
            $table->date('start_date')->nullable();
            
            $table->decimal('default_profit_percent', 5, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('currency_symbol_placement')->default('before'); // before, after

            $table->string('time_zone')->default('UTC');
            $table->string('logo_path')->nullable();
            $table->integer('financial_year_start_month')->default(1); // 1-12
            
            $table->string('stock_accounting_method')->default('fifo'); // fifo, lifo, etc.
            $table->integer('transaction_edit_days')->default(30);
            $table->string('date_format')->default('Y-m-d');

            $table->string('time_format')->default('24'); // 12, 24
            $table->integer('currency_precision')->default(2);
            $table->integer('quantity_precision')->default(2);

            // Contact & Info
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('invoice_footer_text')->nullable();

            // Tax Tab
            $table->string('tax_identification_number')->nullable();
            $table->string('tax_1_name')->nullable();
            $table->string('tax_1_no')->nullable();
            $table->string('tax_2_name')->nullable();
            $table->string('tax_2_no')->nullable();

            // Products Tab
            $table->string('sku_prefix')->nullable();
            $table->boolean('enable_product_expiry')->default(false);
            $table->boolean('enable_category')->default(true);
            $table->string('default_unit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
