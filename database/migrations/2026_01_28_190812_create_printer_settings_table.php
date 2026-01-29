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
        Schema::create('printer_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['kitchen', 'customer'])->unique();
            $table->string('name', 100);                          // 'Impresora Cocina', 'Impresora Caja'
            $table->enum('connection_type', ['network', 'usb', 'windows_share'])->default('network');
            $table->string('ip_address', 45)->nullable();         // IPv4 o IPv6
            $table->integer('port')->nullable()->default(9100);
            $table->string('printer_name', 255)->nullable();      // Para USB/Windows
            $table->enum('width_mm', ['58', '80'])->default('80');
            $table->boolean('is_enabled')->default(false);
            $table->boolean('auto_print')->default(true);         // Imprimir automáticamente
            $table->text('test_message')->nullable();             // Mensaje para prueba de impresión
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_settings');
    }
};
