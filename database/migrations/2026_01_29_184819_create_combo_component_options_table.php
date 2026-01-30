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
        Schema::create('combo_component_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_component_id')->constrained()->onDelete('cascade');

            // Referencia polimórfica al producto (MenuItem o SimpleProduct)
            $table->string('sellable_type'); // 'menu_item' o 'simple_product'
            $table->unsignedBigInteger('sellable_id');

            // Ajuste de precio (puede ser positivo, negativo o cero)
            // Ej: Soda Lata +$1.00, Agua -$0.50, Cascada $0.00
            $table->decimal('price_adjustment', 10, 2)->default(0);

            // Si esta es la opción por defecto (preseleccionada)
            $table->boolean('is_default')->default(false);

            // Si esta opción está disponible
            $table->boolean('is_available')->default(true);

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
        Schema::dropIfExists('combo_component_options');
    }
};
