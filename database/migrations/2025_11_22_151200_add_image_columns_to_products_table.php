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
            $table->string('image_uri1')->nullable();
            $table->string('image_uri2')->nullable();
            $table->string('image_uri3')->nullable();
            $table->string('image_uri4')->nullable();
            $table->string('image_uri5')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image_uri1', 'image_uri2', 'image_uri3', 'image_uri4', 'image_uri5']);
        });
    }
};
