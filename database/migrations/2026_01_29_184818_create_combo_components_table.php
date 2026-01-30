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
        Schema::create('combo_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained()->onDelete('cascade');

            // Tipo de componente: fixed (siempre incluido) o choice (cliente elige)
            $table->enum('component_type', ['fixed', 'choice'])->default('fixed');

            // Nombre del grupo de elección (solo para choice), ej: "Bebida", "Acompañamiento"
            $table->string('name')->nullable();

            // Cantidad de este componente (ej: 1 bebida, 2 tacos)
            $table->integer('quantity')->default(1);

            // Si es requerido (para choice) - si false, el cliente puede omitirlo
            $table->boolean('is_required')->default(true);

            // Para componentes FIJOS: referencia directa al producto
            // sellable_type: 'menu_item' o 'simple_product'
            $table->string('sellable_type')->nullable();
            $table->unsignedBigInteger('sellable_id')->nullable();

            // Orden de aparición
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Índice para búsqueda polimórfica
            $table->index(['sellable_type', 'sellable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_components');
    }
};
