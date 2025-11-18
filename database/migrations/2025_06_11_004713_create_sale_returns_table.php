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
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();

            // Relación con la venta original
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('processed_by_user_id')->constrained('users')->onDelete('cascade');

            // Información de la devolución
            $table->string('return_number')->unique(); // RET20250610001
            $table->enum('return_type', ['total', 'partial'])->default('partial');
            $table->enum('reason', ['defective', 'wrong_order', 'customer_request', 'error', 'other']);
            $table->text('notes')->nullable();

            // Importes
            $table->decimal('subtotal_returned', 10, 2)->default(0);
            $table->decimal('tax_returned', 10, 2)->default(0);
            $table->decimal('total_returned', 10, 2);

            // Estado y control
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->enum('refund_method', ['efectivo', 'tarjeta', 'transferencia', 'credito'])->default('efectivo');
            $table->boolean('inventory_restored')->default(false);
            $table->boolean('cash_flow_adjusted')->default(false);

            // Fechas
            $table->date('return_date');
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Índices básicos
            $table->index(['sale_id', 'status']);
            $table->index('return_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_returns');
    }
};
