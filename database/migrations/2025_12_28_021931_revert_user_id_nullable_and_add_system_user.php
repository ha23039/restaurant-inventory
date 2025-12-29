<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear el usuario del sistema si no existe
        $systemUser = User::firstOrCreate(
            ['email' => 'sistema@menudigital.local'],
            [
                'name' => 'Sistema - Menú Digital',
                'password' => bcrypt('sistema-no-login-' . bin2hex(random_bytes(16))),
                'role' => 'cajero',
                'is_active' => false,
            ]
        );

        // 2. Actualizar todas las ventas con user_id NULL para que usen el sistema
        DB::table('sales')
            ->whereNull('user_id')
            ->update(['user_id' => $systemUser->id]);

        // 3. Ahora sí podemos hacer la columna NOT NULL
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Si se revierte, volver a nullable
            $table->foreignId('user_id')->nullable()->change();
        });
    }
};
