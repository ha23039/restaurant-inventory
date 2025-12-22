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
        // Tablas de productos
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });

        Schema::table('menu_item_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });

        Schema::table('simple_products', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });

        // Tablas de operaciones
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });

        Schema::table('cash_flow', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });

        Schema::table('sale_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->default(1)->after('id');
            $table->index('restaurant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'products',
            'categories',
            'menu_items',
            'menu_item_variants',
            'simple_products',
            'recipes',
            'sales',
            'tables',
            'inventory_movements',
            'cash_flow',
            'sale_returns',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['restaurant_id']);
                $table->dropColumn('restaurant_id');
            });
        }
    }
};
