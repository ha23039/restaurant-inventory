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
        Schema::create('simple_product_variant_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simple_product_variant_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_needed', 10, 3);
            $table->string('unit', 20);
            $table->unsignedBigInteger('restaurant_id')->default(1);
            $table->timestamps();

            $table->unique(['simple_product_variant_id', 'product_id'], 'spvr_variant_product_unique');
            $table->index('simple_product_variant_id');
            $table->index('product_id');
            $table->index('restaurant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simple_product_variant_recipes');
    }
};
