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
        Schema::table('sale_items', function (Blueprint $table) {
            // Agregar soporte para variantes de menu items
            $table->foreignId('menu_item_variant_id')->nullable()->after('menu_item_id')->constrained()->onDelete('set null');

            // menu_item_id puede ser null si es una variante
            $table->foreignId('menu_item_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['menu_item_variant_id']);
            $table->dropColumn('menu_item_variant_id');
        });
    }
};
