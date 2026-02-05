<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('product_type');
            $table->foreignId('cancelled_by_user_id')->nullable()->after('cancelled_at')
                ->constrained('users')->onDelete('set null');
            $table->string('cancellation_reason')->nullable()->after('cancelled_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by_user_id']);
            $table->dropColumn(['cancelled_at', 'cancelled_by_user_id', 'cancellation_reason']);
        });
    }
};
