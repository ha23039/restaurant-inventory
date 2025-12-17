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
        Schema::create('menu_item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->string('variant_name'); // "Maiz - Revuelta"
            $table->string('variant_sku')->unique()->nullable(); // "PUP-MAIZ-REV"
            $table->decimal('price', 10, 2);
            $table->json('attributes')->nullable(); // {"masa": "maiz", "relleno": "revuelta"}
            $table->boolean('is_available')->default(true);
            $table->integer('display_order')->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();

            // Indices
            $table->index(['menu_item_id', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_variants');
    }
};
