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
        Schema::create('order_settings', function (Blueprint $table) {
            $table->id();

            // Numeración de pedidos
            $table->string('order_number_prefix', 10)->default('');
            $table->integer('order_number_padding')->default(4);
            $table->enum('order_number_reset', ['never', 'daily', 'monthly', 'yearly'])->default('daily');

            // Tipos de servicio habilitados
            $table->boolean('allow_dine_in')->default(true);
            $table->boolean('allow_takeout')->default(true);
            $table->boolean('allow_delivery')->default(false);
            $table->boolean('allow_scheduled')->default(false);

            // Impuestos
            $table->boolean('tax_included')->default(true);
            $table->decimal('tax_percentage', 5, 2)->default(13.00);
            $table->boolean('show_tax_breakdown')->default(false);

            // Propinas
            $table->boolean('tip_enabled')->default(false);
            $table->json('tip_suggestions')->nullable();  // [10, 15, 20]
            $table->integer('tip_mandatory_above')->nullable();

            // Requisitos de cliente
            $table->boolean('require_customer_name')->default(false);
            $table->boolean('require_customer_phone')->default(false);

            // Notas y tiempo
            $table->boolean('allow_notes_per_item')->default(true);
            $table->integer('default_prep_time_minutes')->default(20);

            // Montos mínimos
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->decimal('delivery_min_amount', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_settings');
    }
};
