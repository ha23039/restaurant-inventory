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
        Schema::table('kitchen_order_states', function (Blueprint $table) {
            // Eliminar la foreign key existente
            $table->dropForeign(['sale_id']);

            // Recrear con cascade delete
            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchen_order_states', function (Blueprint $table) {
            // Eliminar la foreign key con cascade
            $table->dropForeign(['sale_id']);

            // Restaurar sin cascade
            $table->foreign('sale_id')
                ->references('id')
                ->on('sales');
        });
    }
};
