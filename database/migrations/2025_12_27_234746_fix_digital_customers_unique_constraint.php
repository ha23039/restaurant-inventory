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
        Schema::table('digital_customers', function (Blueprint $table) {
            // Eliminar el índice único de solo phone
            $table->dropUnique(['phone']);

            // Crear índice único compuesto de phone + country_code
            $table->unique(['phone', 'country_code'], 'digital_customers_phone_country_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_customers', function (Blueprint $table) {
            // Revertir: eliminar índice compuesto
            $table->dropUnique('digital_customers_phone_country_unique');

            // Restaurar índice único de solo phone
            $table->unique('phone');
        });
    }
};
