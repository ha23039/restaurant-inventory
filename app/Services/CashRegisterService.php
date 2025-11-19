<?php

namespace App\Services;

use App\Models\CashRegisterSession;
use App\Repositories\Interfaces\CashRegisterRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CashRegisterService
{
    public function __construct(
        private CashRegisterRepositoryInterface $repository
    ) {
    }

    /**
     * Abrir nueva sesión de caja
     */
    public function openSession(array $data): CashRegisterSession
    {
        try {
            DB::beginTransaction();

            $userId = $data['user_id'] ?? Auth::id();

            // Validar que el usuario no tenga ya una caja abierta
            if ($this->repository->userHasOpenSession($userId)) {
                throw new Exception('El usuario ya tiene una sesión de caja abierta.');
            }

            // Crear sesión
            $session = $this->repository->create([
                'user_id' => $userId,
                'opening_amount' => $data['opening_amount'],
                'status' => 'open',
                'opened_at' => now(),
                'opening_notes' => $data['opening_notes'] ?? null,
            ]);

            DB::commit();

            Log::info('Sesión de caja abierta', [
                'session_id' => $session->id,
                'user_id' => $userId,
                'opening_amount' => $data['opening_amount'],
            ]);

            return $session->fresh(['user', 'sales']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al abrir sesión de caja', [
                'error' => $e->getMessage(),
                'user_id' => $userId ?? null,
            ]);
            throw $e;
        }
    }

    /**
     * Cerrar sesión de caja
     */
    public function closeSession(int $sessionId, array $data): CashRegisterSession
    {
        try {
            DB::beginTransaction();

            $session = $this->repository->findWithRelations($sessionId);

            if (!$session) {
                throw new Exception('Sesión de caja no encontrada.');
            }

            if ($session->isClosed()) {
                throw new Exception('La sesión de caja ya está cerrada.');
            }

            // Verificar que sea el mismo usuario
            if ($session->user_id !== Auth::id() && !$this->isAdmin()) {
                throw new Exception('No tienes permiso para cerrar esta sesión de caja.');
            }

            // Cerrar sesión con el repositorio (calcula automáticamente diferencias)
            $closedSession = $this->repository->closeSession($sessionId, $data);

            DB::commit();

            Log::info('Sesión de caja cerrada', [
                'session_id' => $closedSession->id,
                'user_id' => $closedSession->user_id,
                'opening_amount' => $closedSession->opening_amount,
                'closing_amount' => $closedSession->closing_amount,
                'expected_amount' => $closedSession->expected_amount,
                'difference' => $closedSession->difference,
            ]);

            return $closedSession;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al cerrar sesión de caja', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            throw $e;
        }
    }

    /**
     * Obtener sesión actual del usuario
     */
    public function getCurrentSession(): ?CashRegisterSession
    {
        return $this->repository->getCurrentSession();
    }

    /**
     * Verificar si el usuario tiene una sesión abierta
     */
    public function hasOpenSession(int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        return $this->repository->userHasOpenSession($userId);
    }

    /**
     * Obtener sesión con detalles
     */
    public function getSessionDetails(int $sessionId): array
    {
        $session = $this->repository->findWithRelations($sessionId);

        if (!$session) {
            throw new Exception('Sesión de caja no encontrada.');
        }

        return [
            'session' => $session,
            'total_cash_sales' => $session->total_cash_sales,
            'total_card_sales' => $session->total_card_sales,
            'total_transfer_sales' => $session->total_transfer_sales,
            'total_all_sales' => $session->total_all_sales,
            'transaction_count' => $session->transaction_count,
            'duration_hours' => $session->current_duration_in_hours,
            'sales' => $session->sales()->with(['saleItems', 'user'])->get(),
        ];
    }

    /**
     * Obtener estadísticas del cajero
     */
    public function getCashierStats(int $userId, string $from, string $to): array
    {
        $sessions = CashRegisterSession::where('user_id', $userId)
            ->whereBetween('opened_at', [$from, $to])
            ->where('status', 'closed')
            ->with('sales')
            ->get();

        $totalSessions = $sessions->count();
        $totalSales = $sessions->sum('total_all_sales');
        $totalDifferences = $sessions->sum('difference');
        $sessionsWithShortage = $sessions->filter(fn($s) => $s->hasShortage())->count();
        $sessionsWithSurplus = $sessions->filter(fn($s) => $s->hasSurplus())->count();
        $perfectSessions = $sessions->filter(fn($s) => !$s->hasDifference())->count();

        return [
            'user_id' => $userId,
            'total_sessions' => $totalSessions,
            'total_sales' => (float) $totalSales,
            'total_differences' => (float) $totalDifferences,
            'sessions_with_shortage' => $sessionsWithShortage,
            'sessions_with_surplus' => $sessionsWithSurplus,
            'perfect_sessions' => $perfectSessions,
            'accuracy_rate' => $totalSessions > 0 ? ($perfectSessions / $totalSessions * 100) : 0,
            'average_session_sales' => $totalSessions > 0 ? ($totalSales / $totalSessions) : 0,
        ];
    }

    /**
     * Validar que se puede procesar una venta
     */
    public function validateCanProcessSale(int $userId): void
    {
        if (!$this->hasOpenSession($userId)) {
            throw new Exception('Debes abrir una caja antes de procesar ventas.');
        }
    }

    /**
     * Verificar si el usuario es admin
     */
    private function isAdmin(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }
}
