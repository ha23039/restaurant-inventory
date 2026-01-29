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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();           // 'efectivo', 'tarjeta', etc.
            $table->string('label', 100);                   // 'Efectivo', 'Tarjeta de Crédito'
            $table->string('icon', 50)->nullable();         // 'cash', 'credit-card', 'bank'
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_reference')->default(false);  // ¿Pide número de referencia?
            $table->boolean('requires_amount_input')->default(false); // ¿Pide monto recibido? (efectivo)
            $table->decimal('commission_percent', 5, 2)->default(0); // Comisión para reportes
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
