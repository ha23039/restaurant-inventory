<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('sale_return_id')->constrained('sale_returns')->onDelete('cascade');
            $table->foreignId('sale_item_id')->constrained('sale_items')->onDelete('cascade');

            // Cantidades
            $table->integer('quantity_returned'); // Cantidad que se devuelve
            $table->integer('original_quantity'); // Cantidad original vendida

            // Precios (copiados del sale_item original)
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);

            // Control de estado
            $table->boolean('inventory_restored')->default(false);

            $table->timestamps();

            // Índices
            $table->index(['sale_return_id', 'sale_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
    }
};
