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
        Schema::table('simple_products', function (Blueprint $table) {
            // Hacer nullable product_id y cost_per_unit para productos con variantes
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->decimal('cost_per_unit', 10, 3)->nullable()->change();
            $table->decimal('sale_price', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simple_products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->decimal('cost_per_unit', 10, 3)->nullable(false)->change();
            $table->decimal('sale_price', 10, 2)->nullable(false)->change();
        });
    }
};
