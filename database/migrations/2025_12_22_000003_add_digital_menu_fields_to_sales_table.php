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
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('source', ['pos', 'digital_menu', 'waiter'])->default('pos')->after('status');
            $table->foreignId('digital_customer_id')->nullable()->after('source')->constrained('digital_customers')->onDelete('set null');
            $table->enum('delivery_method', ['pickup', 'delivery', 'dine_in'])->nullable()->after('digital_customer_id');
            $table->string('customer_phone', 20)->nullable()->after('customer_name');
            $table->text('customer_address')->nullable()->after('customer_phone');
            $table->text('customer_notes')->nullable()->after('notes');
            $table->timestamp('estimated_ready_at')->nullable()->after('customer_notes');

            // Indexes
            $table->index('source');
            $table->index('digital_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['digital_customer_id']);
            $table->dropIndex(['source']);
            $table->dropIndex(['digital_customer_id']);
            $table->dropColumn([
                'source',
                'digital_customer_id',
                'delivery_method',
                'customer_phone',
                'customer_address',
                'customer_notes',
                'estimated_ready_at',
            ]);
        });
    }
};
