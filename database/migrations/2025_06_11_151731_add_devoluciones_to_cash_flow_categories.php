<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar 'devoluciones' al ENUM de categorías en cash_flow
        DB::statement("ALTER TABLE cash_flow MODIFY COLUMN category ENUM('ventas', 'compras', 'gastos_operativos', 'gastos_admin', 'devoluciones', 'otros')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Volver al ENUM original
        DB::statement("ALTER TABLE cash_flow MODIFY COLUMN category ENUM('ventas', 'compras', 'gastos_operativos', 'gastos_admin', 'otros')");
    }
};
