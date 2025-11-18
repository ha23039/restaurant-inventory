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
        // Products - For inventory queries
        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active', 'idx_products_is_active');
            $table->index('current_stock', 'idx_products_current_stock');
            $table->index('expiry_date', 'idx_products_expiry_date');
            $table->index(['current_stock', 'min_stock'], 'idx_products_low_stock');
        });

        // Menu Items - For menu availability queries
        Schema::table('menu_items', function (Blueprint $table) {
            $table->index('is_available', 'idx_menu_items_is_available');
            $table->index(['category_id', 'is_available'], 'idx_menu_items_category_available');
        });

        // Simple Products - For simple product queries
        Schema::table('simple_products', function (Blueprint $table) {
            $table->index('is_available', 'idx_simple_products_is_available');
            $table->index(['category_id', 'is_available'], 'idx_simple_products_category_available');
        });

        // Sales - For sales reports and queries
        Schema::table('sales', function (Blueprint $table) {
            $table->index('status', 'idx_sales_status');
            $table->index('payment_method', 'idx_sales_payment_method');
            $table->index('created_at', 'idx_sales_created_at');
            $table->index(['user_id', 'created_at'], 'idx_sales_user_date');
        });

        // Sale Items - For sale details queries
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('product_type', 'idx_sale_items_product_type');
            $table->index('menu_item_id', 'idx_sale_items_menu_item_id');
            $table->index('simple_product_id', 'idx_sale_items_simple_product_id');
        });

        // Inventory Movements - For inventory tracking
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index('movement_type', 'idx_inventory_movements_type');
            $table->index('reason', 'idx_inventory_movements_reason');
            $table->index('movement_date', 'idx_inventory_movements_date');
            $table->index(['product_id', 'movement_date'], 'idx_inventory_movements_product_date');
        });

        // Cash Flow - For financial reports
        Schema::table('cash_flow', function (Blueprint $table) {
            $table->index('type', 'idx_cash_flow_type');
            $table->index('category', 'idx_cash_flow_category');
            $table->index('flow_date', 'idx_cash_flow_date');
            $table->index(['type', 'flow_date'], 'idx_cash_flow_type_date');
            $table->index(['category', 'flow_date'], 'idx_cash_flow_category_date');
        });

        // Sale Returns - For returns tracking
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->index('return_date', 'idx_sale_returns_date');
            $table->index('inventory_restored', 'idx_sale_returns_inventory_restored');
            $table->index('cash_flow_adjusted', 'idx_sale_returns_cash_flow_adjusted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Products
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_is_active');
            $table->dropIndex('idx_products_current_stock');
            $table->dropIndex('idx_products_expiry_date');
            $table->dropIndex('idx_products_low_stock');
        });

        // Menu Items
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex('idx_menu_items_is_available');
            $table->dropIndex('idx_menu_items_category_available');
        });

        // Simple Products
        Schema::table('simple_products', function (Blueprint $table) {
            $table->dropIndex('idx_simple_products_is_available');
            $table->dropIndex('idx_simple_products_category_available');
        });

        // Sales
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_status');
            $table->dropIndex('idx_sales_payment_method');
            $table->dropIndex('idx_sales_created_at');
            $table->dropIndex('idx_sales_user_date');
        });

        // Sale Items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('idx_sale_items_product_type');
            $table->dropIndex('idx_sale_items_menu_item_id');
            $table->dropIndex('idx_sale_items_simple_product_id');
        });

        // Inventory Movements
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('idx_inventory_movements_type');
            $table->dropIndex('idx_inventory_movements_reason');
            $table->dropIndex('idx_inventory_movements_date');
            $table->dropIndex('idx_inventory_movements_product_date');
        });

        // Cash Flow
        Schema::table('cash_flow', function (Blueprint $table) {
            $table->dropIndex('idx_cash_flow_type');
            $table->dropIndex('idx_cash_flow_category');
            $table->dropIndex('idx_cash_flow_date');
            $table->dropIndex('idx_cash_flow_type_date');
            $table->dropIndex('idx_cash_flow_category_date');
        });

        // Sale Returns
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropIndex('idx_sale_returns_date');
            $table->dropIndex('idx_sale_returns_inventory_restored');
            $table->dropIndex('idx_sale_returns_cash_flow_adjusted');
        });
    }
};
