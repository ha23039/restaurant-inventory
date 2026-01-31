<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTA: Esta migración es idempotente - verifica si las columnas existen
     * antes de crearlas para evitar errores si ya fueron creadas por una
     * migración anterior con diferente timestamp.
     */
    public function up(): void
    {
        // Campos de visibilidad para menu_items
        if (Schema::hasTable('menu_items')) {
            Schema::table('menu_items', function (Blueprint $table) {
                if (!Schema::hasColumn('menu_items', 'show_in_digital_menu')) {
                    $table->boolean('show_in_digital_menu')->default(true)->after('is_available');
                }
                if (!Schema::hasColumn('menu_items', 'available_in_combos')) {
                    $table->boolean('available_in_combos')->default(true)->after('show_in_digital_menu');
                }
            });
        }

        // Campos de visibilidad para simple_products
        if (Schema::hasTable('simple_products')) {
            Schema::table('simple_products', function (Blueprint $table) {
                if (!Schema::hasColumn('simple_products', 'show_in_digital_menu')) {
                    $table->boolean('show_in_digital_menu')->default(true)->after('is_available');
                }
                if (!Schema::hasColumn('simple_products', 'available_in_combos')) {
                    $table->boolean('available_in_combos')->default(true)->after('show_in_digital_menu');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            if (Schema::hasColumn('menu_items', 'show_in_digital_menu')) {
                $table->dropColumn('show_in_digital_menu');
            }
            if (Schema::hasColumn('menu_items', 'available_in_combos')) {
                $table->dropColumn('available_in_combos');
            }
        });

        Schema::table('simple_products', function (Blueprint $table) {
            if (Schema::hasColumn('simple_products', 'show_in_digital_menu')) {
                $table->dropColumn('show_in_digital_menu');
            }
            if (Schema::hasColumn('simple_products', 'available_in_combos')) {
                $table->dropColumn('available_in_combos');
            }
        });
    }
};
