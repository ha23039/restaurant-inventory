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
        Schema::table('tables', function (Blueprint $table) {
            $table->string('qr_code')->unique()->nullable()->after('table_number');
            $table->string('location_description')->nullable()->after('notes');

            // Index
            $table->index('qr_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropIndex(['qr_code']);
            $table->dropColumn(['qr_code', 'location_description']);
        });
    }
};
