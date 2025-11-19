<?php

namespace App\Repositories;

use App\Models\CashRegisterSession;
use App\Repositories\Interfaces\CashRegisterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashRegisterRepository implements CashRegisterRepositoryInterface
{
    /**
     * Obtener sesión abierta de un usuario
     */
    public function getOpenSessionByUser(int $userId): ?CashRegisterSession
    {
        return CashRegisterSession::with(['user', 'sales'])
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();
    }

    /**
     * Verificar si un usuario tiene una sesión abierta
     */
    public function userHasOpenSession(int $userId): bool
    {
        return CashRegisterSession::where('user_id', $userId)
            ->where('status', 'open')
            ->exists();
    }

    /**
     * Crear nueva sesión de caja
     */
    public function create(array $data): CashRegisterSession
    {
        return CashRegisterSession::create($data);
    }

    /**
     * Actualizar sesión de caja
     */
    public function update(int $id, array $data): CashRegisterSession
    {
        $session = CashRegisterSession::findOrFail($id);
        $session->update($data);
        return $session->fresh(['user', 'sales']);
    }

    /**
     * Obtener sesión por ID con relaciones
     */
    public function findWithRelations(int $id): ?CashRegisterSession
    {
        return CashRegisterSession::with(['user', 'sales'])
            ->find($id);
    }

    /**
     * Obtener historial de sesiones con paginación
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CashRegisterSession::with(['user'])
            ->orderBy('opened_at', 'desc');

        // Filtrar por estado
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filtrar por usuario
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filtrar por rango de fechas
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('opened_at', [
                $filters['date_from'],
                $filters['date_to']
            ]);
        }

        // Filtrar por diferencias
        if (isset($filters['has_difference']) && $filters['has_difference']) {
            $query->where(function ($q) {
                $q->where('difference', '>', 0)
                    ->orWhere('difference', '<', 0);
            });
        }

        // Filtrar por faltantes
        if (isset($filters['has_shortage']) && $filters['has_shortage']) {
            $query->where('difference', '<', 0);
        }

        // Filtrar por sobrantes
        if (isset($filters['has_surplus']) && $filters['has_surplus']) {
            $query->where('difference', '>', 0);
        }

        return $query->paginate($perPage);
    }

    /**
     * Obtener sesiones de un usuario con paginación
     */
    public function getUserSessions(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return CashRegisterSession::with(['user', 'sales'])
            ->where('user_id', $userId)
            ->orderBy('opened_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener estadísticas de sesiones por rango de fechas
     */
    public function getStatsByDateRange(string $from, string $to): array
    {
        $sessions = CashRegisterSession::with('sales')
            ->whereBetween('opened_at', [$from, $to])
            ->where('status', 'closed')
            ->get();

        $totalSessions = $sessions->count();
        $totalSales = $sessions->sum('total_all_sales');
        $totalCashSales = $sessions->sum('total_cash_sales');
        $totalDifferences = $sessions->sum('difference');
        $sessionsWithShortage = $sessions->filter(fn($s) => $s->hasShortage())->count();
        $sessionsWithSurplus = $sessions->filter(fn($s) => $s->hasSurplus())->count();
        $totalShortage = $sessions->filter(fn($s) => $s->hasShortage())->sum('difference');
        $totalSurplus = $sessions->filter(fn($s) => $s->hasSurplus())->sum('difference');

        return [
            'total_sessions' => $totalSessions,
            'total_sales' => (float) $totalSales,
            'total_cash_sales' => (float) $totalCashSales,
            'total_differences' => (float) $totalDifferences,
            'sessions_with_shortage' => $sessionsWithShortage,
            'sessions_with_surplus' => $sessionsWithSurplus,
            'total_shortage' => (float) $totalShortage,
            'total_surplus' => (float) $totalSurplus,
            'average_difference' => $totalSessions > 0 ? ($totalDifferences / $totalSessions) : 0,
        ];
    }

    /**
     * Obtener sesiones con diferencias (faltantes/sobrantes)
     */
    public function getSessionsWithDifferences(): Collection
    {
        return CashRegisterSession::with(['user'])
            ->where('status', 'closed')
            ->where(function ($query) {
                $query->where('difference', '>', 0)
                    ->orWhere('difference', '<', 0);
            })
            ->orderBy('opened_at', 'desc')
            ->get();
    }

    /**
     * Obtener sesión actual (abierta) del usuario autenticado
     */
    public function getCurrentSession(): ?CashRegisterSession
    {
        if (!Auth::check()) {
            return null;
        }

        return $this->getOpenSessionByUser(Auth::id());
    }

    /**
     * Cerrar sesión de caja
     */
    public function closeSession(int $id, array $data): CashRegisterSession
    {
        $session = CashRegisterSession::with(['user', 'sales'])->findOrFail($id);

        // Calcular expected_amount (ventas en efectivo)
        $expectedAmount = $session->total_cash_sales;

        // Calcular diferencia
        $closingAmount = $data['closing_amount'];
        $totalExpected = $session->opening_amount + $expectedAmount;
        $difference = $closingAmount - $totalExpected;

        // Actualizar sesión
        $session->update([
            'closing_amount' => $closingAmount,
            'expected_amount' => $expectedAmount,
            'difference' => $difference,
            'status' => 'closed',
            'closed_at' => now(),
            'closing_notes' => $data['closing_notes'] ?? null,
        ]);

        return $session->fresh(['user', 'sales']);
    }
}
