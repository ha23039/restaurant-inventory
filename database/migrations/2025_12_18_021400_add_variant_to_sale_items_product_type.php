<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar 'variant' al ENUM de product_type
        DB::statement("ALTER TABLE `sale_items` MODIFY COLUMN `product_type` ENUM('menu', 'simple', 'free', 'variant') NOT NULL DEFAULT 'menu'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los valores anteriores
        DB::statement("ALTER TABLE `sale_items` MODIFY COLUMN `product_type` ENUM('menu', 'simple', 'free') NOT NULL");
    }
};
