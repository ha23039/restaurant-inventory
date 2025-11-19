<?php

namespace App\Repositories\Interfaces;

use App\Models\CashRegisterSession;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CashRegisterRepositoryInterface
{
    /**
     * Obtener sesión abierta de un usuario
     */
    public function getOpenSessionByUser(int $userId): ?CashRegisterSession;

    /**
     * Verificar si un usuario tiene una sesión abierta
     */
    public function userHasOpenSession(int $userId): bool;

    /**
     * Crear nueva sesión de caja
     */
    public function create(array $data): CashRegisterSession;

    /**
     * Actualizar sesión de caja
     */
    public function update(int $id, array $data): CashRegisterSession;

    /**
     * Obtener sesión por ID con relaciones
     */
    public function findWithRelations(int $id): ?CashRegisterSession;

    /**
     * Obtener historial de sesiones con paginación
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Obtener sesiones de un usuario con paginación
     */
    public function getUserSessions(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Obtener estadísticas de sesiones por rango de fechas
     */
    public function getStatsByDateRange(string $from, string $to): array;

    /**
     * Obtener sesiones con diferencias (faltantes/sobrantes)
     */
    public function getSessionsWithDifferences(): Collection;

    /**
     * Obtener sesión actual (abierta) del usuario autenticado
     */
    public function getCurrentSession(): ?CashRegisterSession;

    /**
     * Cerrar sesión de caja
     */
    public function closeSession(int $id, array $data): CashRegisterSession;
}
