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
        Schema::create('kitchen_order_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['nueva', 'preparando', 'lista', 'entregada'])->default('nueva');
            $table->timestamp('started_at')->nullable(); // Cuando empieza preparación
            $table->timestamp('completed_at')->nullable(); // Cuando termina preparación
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->onDelete('set null'); // Chef asignado
            $table->integer('priority')->default(0); // Prioridad (0 = normal, 1 = alta)
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('status');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_order_states');
    }
};
