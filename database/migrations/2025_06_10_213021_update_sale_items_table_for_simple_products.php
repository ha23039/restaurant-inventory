<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Hacer menu_item_id nullable para permitir productos simples
            $table->foreignId('menu_item_id')->nullable()->change();

            // Agregar referencia a productos simples
            $table->foreignId('simple_product_id')->nullable()->constrained()->onDelete('cascade');

            // Agregar tipo de producto para distinguir
            $table->enum('product_type', ['menu', 'simple'])->default('menu');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['simple_product_id']);
            $table->dropColumn(['simple_product_id', 'product_type']);

            // Restaurar menu_item_id como no nullable
            $table->foreignId('menu_item_id')->nullable(false)->change();
        });
    }
};
