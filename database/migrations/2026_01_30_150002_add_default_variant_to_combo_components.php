<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTA: Esta migración es idempotente - verifica si la columna existe
     * antes de crearla para evitar errores si ya fue creada por una
     * migración anterior con diferente timestamp.
     */
    public function up(): void
    {
        if (Schema::hasTable('combo_components') && !Schema::hasColumn('combo_components', 'default_variant_id')) {
            Schema::table('combo_components', function (Blueprint $table) {
                // Para componentes fijos: si el producto tiene variantes, cuál usar por defecto
                $table->unsignedBigInteger('default_variant_id')->nullable()->after('sellable_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('combo_components', 'default_variant_id')) {
            Schema::table('combo_components', function (Blueprint $table) {
                $table->dropColumn('default_variant_id');
            });
        }
    }
};
