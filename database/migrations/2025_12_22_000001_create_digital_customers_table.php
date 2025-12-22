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
        Schema::create('digital_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id')->default(1);
            $table->string('phone', 20)->unique();
            $table->string('country_code', 5)->default('+503');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('verification_code', 10)->nullable();
            $table->timestamp('code_expires_at')->nullable();
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0.00);
            $table->timestamp('last_order_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('phone');
            $table->index('is_verified');
            $table->index('is_active');
            $table->index('restaurant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_customers');
    }
};
