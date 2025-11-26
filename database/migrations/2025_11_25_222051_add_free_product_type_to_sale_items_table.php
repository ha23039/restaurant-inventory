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
        // Para MySQL, necesitamos modificar el ENUM
        DB::statement("ALTER TABLE `sale_items` MODIFY COLUMN `product_type` ENUM('menu', 'simple', 'free') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los valores originales
        DB::statement("ALTER TABLE `sale_items` MODIFY COLUMN `product_type` ENUM('menu', 'simple') NOT NULL");
    }
};
