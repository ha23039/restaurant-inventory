<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class NotificationService
{
    public function create(
        string $type,
        string $message,
        ?string $title = null,
        ?Model $notifiable = null,
        ?array $data = null,
        $users = null
    ): Collection {
        $users = $this->resolveUsers($users);
        $notifications = collect();

        foreach ($users as $user) {
            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'notifiable_type' => $notifiable ? get_class($notifiable) : null,
                'notifiable_id' => $notifiable?->id,
                'data' => $data,
            ]);

            $notifications->push($notification);
        }

        return $notifications;
    }

    public function success(string $message, ?string $title = null, ?Model $notifiable = null, $users = null): Collection
    {
        return $this->create('success', $message, $title, $notifiable, null, $users);
    }

    public function error(string $message, ?string $title = null, ?Model $notifiable = null, $users = null): Collection
    {
        return $this->create('error', $message, $title, $notifiable, null, $users);
    }

    public function warning(string $message, ?string $title = null, ?Model $notifiable = null, $users = null): Collection
    {
        return $this->create('warning', $message, $title, $notifiable, null, $users);
    }

    public function info(string $message, ?string $title = null, ?Model $notifiable = null, $users = null): Collection
    {
        return $this->create('info', $message, $title, $notifiable, null, $users);
    }

    public function lowStock(string $productName, int $currentStock, int $minStock, $users = null): Collection
    {
        return $this->warning(
            "El producto '{$productName}' tiene stock bajo ({$currentStock} unidades). Mínimo requerido: {$minStock}",
            'Stock Bajo',
            null,
            $users ?? $this->getAdminAndAlmaceneroUsers()
        );
    }

    public function saleCompleted(string $saleNumber, float $total, $users = null): Collection
    {
        return $this->success(
            "Nueva venta de $" . number_format($total, 2) . " registrada",
            "Venta #{$saleNumber} Completada",
            null,
            $users ?? $this->getAdminAndCajeroUsers()
        );
    }

    public function returnProcessed(string $returnNumber, float $amount, $users = null): Collection
    {
        return $this->info(
            "Se procesó una devolución de $" . number_format($amount, 2),
            "Devolución #{$returnNumber}",
            null,
            $users ?? $this->getAdminAndCajeroUsers()
        );
    }

    public function productExpiringSoon(string $productName, string $expiryDate, $users = null): Collection
    {
        return $this->warning(
            "El producto '{$productName}' vence el {$expiryDate}",
            'Producto por Vencer',
            null,
            $users ?? $this->getAdminAndAlmaceneroUsers()
        );
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);
    }

    public function deleteNotification(int $notificationId, int $userId): bool
    {
        return Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function clearAll(int $userId): int
    {
        return Notification::where('user_id', $userId)->delete();
    }

    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('read', false)
            ->count();
    }

    public function getRecent(int $userId, int $limit = 10): Collection
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function resolveUsers($users): Collection
    {
        if ($users === null) {
            return User::where('is_active', true)->get();
        }

        if ($users instanceof User) {
            return collect([$users]);
        }

        if ($users instanceof Collection) {
            return $users;
        }

        if (is_array($users)) {
            return collect($users);
        }

        if (is_int($users)) {
            $user = User::find($users);
            return $user ? collect([$user]) : collect();
        }

        return collect();
    }

    private function getAdminAndAlmaceneroUsers(): Collection
    {
        return User::whereIn('role', ['admin', 'almacenero'])
            ->where('is_active', true)
            ->get();
    }

    private function getAdminAndCajeroUsers(): Collection
    {
        return User::whereIn('role', ['admin', 'cajero'])
            ->where('is_active', true)
            ->get();
    }

    private function getAdminUsers(): Collection
    {
        return User::where('role', 'admin')
            ->where('is_active', true)
            ->get();
    }
}
