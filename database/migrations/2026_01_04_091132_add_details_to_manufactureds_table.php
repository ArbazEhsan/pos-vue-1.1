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
        Schema::table('manufactureds', function (Blueprint $table) {
            $table->string('reference_no')->nullable()->after('id');
            $table->unsignedBigInteger('recipe_id')->nullable()->after('product_id'); // Link to RecipeHeader
            $table->decimal('quantity', 15, 2)->default(0)->after('product_id'); // Output Qty
            $table->decimal('wasted_quantity', 15, 2)->default(0)->after('quantity');
            $table->decimal('production_cost', 15, 2)->default(0)->after('wasted_quantity');
            $table->decimal('total_cost', 15, 2)->default(0)->after('production_cost');
            $table->string('status')->default('completed')->after('subtract_qty_stage');
            // Assuming 'day' exists and is date, we might want a full timestamp or just use day. 
            // The user asked for "Manufacturing Date+time".
            // Adding a timestamp column if needed, or we rely on created_at or `day` (if changed to datetime).
            // Let's add manufacturing_date as timestamp for precision if 'day' is just date.
            $table->timestamp('manufacturing_date')->nullable()->after('day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manufactureds', function (Blueprint $table) {
            $table->dropColumn([
                'reference_no', 
                'recipe_id', 
                'quantity', 
                'wasted_quantity', 
                'production_cost', 
                'total_cost', 
                'status',
                'manufacturing_date'
            ]);
        });
    }
};
