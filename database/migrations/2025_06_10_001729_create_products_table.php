<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit_type'); // kg, lt, pcs, g, ml, etc
            $table->decimal('current_stock', 10, 3)->default(0);
            $table->decimal('min_stock', 10, 3)->default(0);
            $table->decimal('max_stock', 10, 3)->nullable();
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};