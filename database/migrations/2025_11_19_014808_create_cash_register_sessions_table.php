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
        Schema::create('cash_register_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Cajero responsable
            $table->decimal('opening_amount', 10, 2); // Efectivo inicial en caja
            $table->decimal('closing_amount', 10, 2)->nullable(); // Efectivo final contado
            $table->decimal('expected_amount', 10, 2)->nullable(); // Ventas en efectivo esperadas
            $table->decimal('difference', 10, 2)->nullable(); // Diferencia (sobrante/faltante)
            $table->enum('status', ['open', 'closed'])->default('open'); // Estado de la caja
            $table->timestamp('opened_at'); // Momento de apertura
            $table->timestamp('closed_at')->nullable(); // Momento de cierre
            $table->text('opening_notes')->nullable(); // Notas al abrir
            $table->text('closing_notes')->nullable(); // Notas al cerrar (explicar diferencias)
            $table->timestamps();

            // Indexes para queries rápidas
            $table->index('status', 'idx_cash_register_status');
            $table->index('user_id', 'idx_cash_register_user');
            $table->index('opened_at', 'idx_cash_register_opened_at');
            $table->index(['user_id', 'status'], 'idx_cash_register_user_status');
        });

        // Agregar foreign key a sales para vincular con sesión de caja
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('cash_register_session_id')
                ->nullable()
                ->after('user_id')
                ->constrained('cash_register_sessions')
                ->onDelete('set null');

            $table->index('cash_register_session_id', 'idx_sales_cash_register');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key from sales first
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['cash_register_session_id']);
            $table->dropIndex('idx_sales_cash_register');
            $table->dropColumn('cash_register_session_id');
        });

        Schema::dropIfExists('cash_register_sessions');
    }
};
