<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_needed', 10, 3); // Cantidad exacta necesaria
            $table->string('unit'); // kg, g, ml, lt, pcs
            $table->timestamps();
            
            // Evitar duplicados en la misma receta
            $table->unique(['menu_item_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};