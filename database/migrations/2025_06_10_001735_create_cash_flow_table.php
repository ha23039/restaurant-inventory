<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_flow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['entrada', 'salida']);
            $table->enum('category', ['ventas', 'compras', 'gastos_operativos', 'gastos_admin', 'otros']);
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->text('notes')->nullable();
            $table->date('flow_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_flow');
    }
};