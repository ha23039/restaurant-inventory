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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number')->unique(); // Número de mesa (ej: "1", "A1", "VIP-1")
            $table->string('name')->nullable(); // Nombre opcional (ej: "Mesa VIP 1")
            $table->integer('capacity')->default(4); // Capacidad de personas
            $table->enum('status', ['disponible', 'ocupada', 'reservada', 'en_limpieza'])->default('disponible');
            $table->foreignId('current_sale_id')->nullable()->constrained('sales')->onDelete('set null'); // Venta actual en la mesa
            $table->timestamp('last_occupied_at')->nullable(); // Última vez ocupada
            $table->text('notes')->nullable(); // Notas sobre la mesa
            $table->boolean('is_active')->default(true); // Si la mesa está activa
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
