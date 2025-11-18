<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('simple_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Producto del inventario
            $table->string('name'); // Nombre para venta (ej: "Coca Cola 355ml")
            $table->text('description')->nullable();
            $table->decimal('sale_price', 10, 2); // Precio de venta
            $table->decimal('cost_per_unit', 10, 3)->default(1); // Cuántas unidades del inventario consume
            $table->boolean('is_available')->default(true);
            $table->string('category')->default('individual'); // individual, extra, addon
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simple_products');
    }
};
