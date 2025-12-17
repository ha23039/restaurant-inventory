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
        Schema::table('recipes', function (Blueprint $table) {
            // Agregar soporte para variantes
            $table->foreignId('menu_item_variant_id')->nullable()->after('menu_item_id')->constrained()->onDelete('cascade');

            // Hacer menu_item_id nullable ya que puede ser variante o item normal
            $table->foreignId('menu_item_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropForeign(['menu_item_variant_id']);
            $table->dropColumn('menu_item_variant_id');

            // Revertir menu_item_id a no nullable
            $table->foreignId('menu_item_id')->nullable(false)->change();
        });
    }
};
