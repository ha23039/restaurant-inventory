<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el ENUM para incluir 'mesero'
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'chef', 'almacenero', 'cajero', 'mesero') NOT NULL DEFAULT 'cajero'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al ENUM original (sin mesero)
        // Primero cambiar usuarios mesero a cajero
        DB::table('users')->where('role', 'mesero')->update(['role' => 'cajero']);

        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'chef', 'almacenero', 'cajero') NOT NULL DEFAULT 'cajero'");
    }
};
