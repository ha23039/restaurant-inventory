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
        Schema::create('ticket_settings', function (Blueprint $table) {
            $table->id();

            // Contenido del ticket de cliente
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_address')->default(true);
            $table->boolean('show_phone')->default(true);
            $table->boolean('show_email')->default(false);
            $table->boolean('show_tax_id')->default(true);
            $table->boolean('show_website')->default(false);

            // Código QR
            $table->boolean('show_qr_code')->default(true);
            $table->enum('qr_content', ['sale_number', 'sale_url', 'menu_url', 'custom'])->default('sale_number');
            $table->string('qr_custom_url')->nullable();
            $table->integer('qr_size')->default(150);

            // Mensajes personalizados
            $table->string('header_message', 255)->nullable();         // Mensaje arriba del ticket
            $table->string('footer_message', 255)->default('¡Gracias por su compra!');
            $table->string('promo_message', 255)->nullable();          // Promoción actual

            // Ticket de cocina
            $table->boolean('kitchen_show_customer_name')->default(false);
            $table->boolean('kitchen_show_table_number')->default(true);
            $table->boolean('kitchen_show_notes')->default(true);
            $table->boolean('kitchen_show_order_number')->default(true);
            $table->enum('kitchen_font_size', ['normal', 'large'])->default('large');

            // Categorías que NO van a cocina (JSON array)
            $table->json('non_kitchen_categories')->nullable();

            // Configuración de prioridades
            $table->integer('priority_high_threshold')->default(10);   // items o más = ALTA
            $table->integer('priority_medium_threshold')->default(5);  // items o más = MEDIA
            $table->string('rush_hours_start', 5)->default('12:00');
            $table->string('rush_hours_end', 5)->default('14:00');
            $table->string('rush_hours_evening_start', 5)->default('19:00');
            $table->string('rush_hours_evening_end', 5)->default('21:00');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_settings');
    }
};
