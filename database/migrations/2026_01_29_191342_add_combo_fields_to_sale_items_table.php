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
            // Campo para referencia al combo
            $table->unsignedBigInteger('combo_id')->nullable()->after('simple_product_variant_id');
            $table->foreign('combo_id')->references('id')->on('combos')->onDelete('set null');

            // Campo JSON para almacenar las selecciones del combo
            $table->json('combo_selections')->nullable()->after('combo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['combo_id']);
            $table->dropColumn(['combo_id', 'combo_selections']);
        });
    }
};
