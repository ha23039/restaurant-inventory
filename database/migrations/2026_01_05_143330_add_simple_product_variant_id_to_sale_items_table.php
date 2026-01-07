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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('simple_product_variant_id')
                ->nullable()
                ->after('simple_product_id')
                ->constrained('simple_product_variants')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['simple_product_variant_id']);
            $table->dropColumn('simple_product_variant_id');
        });
    }
};
