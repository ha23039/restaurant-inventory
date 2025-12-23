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
        Schema::create('simple_product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simple_product_id')->constrained()->onDelete('cascade');
            $table->string('variant_name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->json('attributes')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedBigInteger('restaurant_id')->default(1);
            $table->timestamps();

            $table->index('simple_product_id');
            $table->index('restaurant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simple_product_variants');
    }
};
