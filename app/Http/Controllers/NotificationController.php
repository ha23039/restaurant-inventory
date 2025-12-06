<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $userId = Auth::id();

        $notifications = $this->notificationService->getRecent($userId, $limit);

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'timestamp' => $notification->created_at->toIso8601String(),
                    'time_ago' => $notification->time_ago,
                    'read' => $notification->read,
                    'data' => $notification->data,
                ];
            }),
            'unread_count' => $this->notificationService->getUnreadCount($userId),
        ]);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $success = $this->notificationService->markAsRead($id, Auth::id());

        if (!$success) {
            return response()->json([
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Notification marked as read',
            'unread_count' => $this->notificationService->getUnreadCount(Auth::id()),
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(Auth::id());

        return response()->json([
            'message' => "Marked {$count} notifications as read",
            'count' => $count,
            'unread_count' => 0,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $success = $this->notificationService->deleteNotification($id, Auth::id());

        if (!$success) {
            return response()->json([
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Notification deleted',
            'unread_count' => $this->notificationService->getUnreadCount(Auth::id()),
        ]);
    }

    public function clearAll(): JsonResponse
    {
        $count = $this->notificationService->clearAll(Auth::id());

        return response()->json([
            'message' => "Cleared {$count} notifications",
            'count' => $count,
            'unread_count' => 0,
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread_count' => $this->notificationService->getUnreadCount(Auth::id()),
        ]);
    }
}
