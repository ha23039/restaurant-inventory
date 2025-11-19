<?php

namespace App\Http\Middleware;

use App\Services\CashRegisterService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidateCashRegister
{
    public function __construct(
        private CashRegisterService $cashRegisterService
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Solo aplicar validación a cajeros y admins
        if (!$user || !in_array($user->role, ['cajero', 'admin'])) {
            return $next($request);
        }

        // Verificar si tiene una sesión de caja abierta
        if (!$this->cashRegisterService->hasOpenSession($user->id)) {
            // Si es una petición API/AJAX, retornar JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Debes abrir una caja antes de procesar ventas.',
                    'error' => 'NO_CASH_REGISTER_SESSION',
                    'redirect' => route('cashregister.create'),
                ], 403);
            }

            // Si es una petición web, redirigir
            return redirect()
                ->route('cashregister.create')
                ->with('error', 'Debes abrir una caja antes de procesar ventas.');
        }

        return $next($request);
    }
}
