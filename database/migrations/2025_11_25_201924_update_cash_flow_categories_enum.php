<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para SQLite, necesitamos recrear la tabla
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite no soporta ALTER COLUMN, así que no hacemos nada
            // Las validaciones se manejan en la capa de aplicación
            return;
        }

        // Para MySQL
        DB::statement("ALTER TABLE cash_flow MODIFY COLUMN category ENUM(
            'ventas',
            'compras',
            'servicios_publicos',
            'compra_productos_insumos',
            'arriendo',
            'nomina',
            'gastos_operativos',
            'gastos_admin',
            'marketing',
            'transporte_domicilios',
            'mantenimiento_reparaciones',
            'muebles_equipos',
            'devoluciones',
            'otros'
        ) NOT NULL DEFAULT 'otros'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        // Revertir a las categorías anteriores
        DB::statement("ALTER TABLE cash_flow MODIFY COLUMN category ENUM(
            'ventas',
            'compras',
            'gastos_operativos',
            'gastos_admin',
            'devoluciones',
            'otros'
        ) NOT NULL DEFAULT 'otros'");
    }
};
