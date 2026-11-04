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
        Schema::table('products', function (Blueprint $table) {
            $table->longText('description')->nullable();
            $table->string('warranty_period')->nullable();
            $table->string('warranty_type')->nullable();
            $table->date('expiry_date')->nullable();
            
            // Shipping
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit')->default('kg')->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();

            // Variation Config
            $table->string('variation_name')->nullable();
            $table->boolean('include_image')->default(false);
            
            // image_uri columns already exist
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->string('var_sku')->nullable();
            $table->decimal('var_wholesale_price', 10, 2)->default(0);
            $table->decimal('var_purchase_price', 10, 2)->default(0);
            $table->decimal('var_retail_price', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn(['var_sku', 'var_purchase_price', 'var_wholesale_price']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'description', 
                'warranty_period', 
                'warranty_type', 
                'expiry_date',
                'weight',
                'weight_unit',
                'height',
                'width',
                'length',
                'variation_name',
                'include_image',
            ]);
        });
    }
};
