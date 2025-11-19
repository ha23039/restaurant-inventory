<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the database driver
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // MySQL: Use MODIFY COLUMN
            DB::statement("ALTER TABLE cash_flow MODIFY COLUMN category ENUM('ventas', 'compras', 'gastos_operativos', 'gastos_admin', 'devoluciones', 'otros')");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Drop and recreate column
            // First, add a temporary column
            Schema::table('cash_flow', function (Blueprint $table) {
                $table->enum('category_new', ['ventas', 'compras', 'gastos_operativos', 'gastos_admin', 'devoluciones', 'otros'])->nullable()->after('category');
            });

            // Copy data from old column to new column
            DB::statement("UPDATE cash_flow SET category_new = category::text");

            // Drop old column and rename new column
            Schema::table('cash_flow', function (Blueprint $table) {
                $table->dropColumn('category');
            });

            Schema::table('cash_flow', function (Blueprint $table) {
                $table->renameColumn('category_new', 'category');
            });
        } else {
            // SQLite: Drop and recreate column (SQLite doesn't support enum natively)
            Schema::table('cash_flow', function (Blueprint $table) {
                $table->string('category_temp')->nullable();
            });

            DB::statement("UPDATE cash_flow SET category_temp = category");

            Schema::table('cash_flow', function (Blueprint $table) {
                $table->dropColumn('category');
            });

            Schema::table('cash_flow', function (Blueprint $table) {
                $table->enum('category', ['ventas', 'compras', 'gastos_operativos', 'gastos_admin', 'devoluciones', 'otros']);
            });

            DB::statement("UPDATE cash_flow SET category = category_temp");

            Schema::table('cash_flow', function (Blueprint $table) {
                $table->dropColumn('category_temp');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE cash_flow MODIFY COLUMN category ENUM('ventas', 'compras', 'gastos_operativos', 'gastos_admin', 'otros')");
        } elseif ($driver === 'pgsql') {
            Schema::table('cash_flow', function (Blueprint $table) {
                $table->enum('category_new', ['ventas', 'compras', 'gastos_operativos', 'gastos_admin', 'otros'])->nullable()->after('category');
            });

            DB::statement("UPDATE cash_flow SET category_new = category::text WHERE category != 'devoluciones'");

            Schema::table('cash_flow', function (Blueprint $table) {
                $table->dropColumn('category');
            });

            Schema::table('cash_flow', function (Blueprint $table) {
                $table->renameColumn('category_new', 'category');
            });
        } else {
            // SQLite
            Schema::table('cash_flow', function (Blueprint $table) {
                $table->string('category_temp')->nullable();
            });

            DB::statement("UPDATE cash_flow SET category_temp = category WHERE category != 'devoluciones'");

            Schema::table('cash_flow', function (Blueprint $table) {
                $table->dropColumn('category');
            });

            Schema::table('cash_flow', function (Blueprint $table) {
                $table->enum('category', ['ventas', 'compras', 'gastos_operativos', 'gastos_admin', 'otros']);
            });

            DB::statement("UPDATE cash_flow SET category = category_temp");

            Schema::table('cash_flow', function (Blueprint $table) {
                $table->dropColumn('category_temp');
            });
        }
    }
};
