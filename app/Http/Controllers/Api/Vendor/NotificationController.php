<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\PaginationResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Get vendor notifications
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $type = $request->get('type'); // 'read', 'unread', or null for all
            $notificationType = $request->get('notification_type'); // e.g., 'booking_status_updated'

            $query = auth()->guard('vendor')->user()->notifications();

            if ($type === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($type === 'unread') {
                $query->whereNull('read_at');
            }

            if ($notificationType) {
                $query->where('type', 'like', '%' . $notificationType . '%');
            }

            $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return $this->successResponse([
                'notifications' => NotificationResource::collection($notifications),
                'pagination' => new PaginationResource($notifications),
                'unread_count' => auth()->guard('vendor')->user()->unreadNotifications()->count(),
            ], 'message.success');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get specific notification
     */
    public function show(string $id): JsonResponse
    {
        try {
            $notification = auth()->guard('vendor')->user()->notifications()->findOrFail($id);

            // Mark as read if not already read
            if (!$notification->read_at) {
                $notification->markAsRead();
            }

            return $this->successResponse([
                'notification' => new NotificationResource($notification),
            ], 'message.success');
        } catch (\Exception $e) {
            return $this->errorResponse('message.record_not_found', 404);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $id): JsonResponse
    {
        try {
            $notification = auth()->guard('vendor')->user()->notifications()->findOrFail($id);

            if (!$notification->read_at) {
                $notification->markAsRead();
            }

            return $this->successResponse([
                'notification' => new NotificationResource($notification),
            ], 'message.success');
        } catch (\Exception $e) {
            return $this->errorResponse('message.record_not_found', 404);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            auth()->guard('vendor')->user()->unreadNotifications->markAsRead();

            return $this->successResponse([
                'message' => 'notifications.marked_all_as_read',
                'unread_count' => 0,
            ], 'message.success');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Delete notification
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $notification = auth()->guard('vendor')->user()->notifications()->findOrFail($id);
            $notification->delete();

            return $this->successResponse([
                'message' => 'notifications.deleted',
            ], 'message.success');
        } catch (\Exception $e) {
            return $this->errorResponse('message.record_not_found', 404);
        }
    }

    /**
     * Get notification statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $vendor = auth()->guard('vendor')->user();

            $stats = [
                'total' => $vendor->notifications()->count(),
                'unread' => $vendor->unreadNotifications()->count(),
                'read' => $vendor->notifications()->whereNotNull('read_at')->count(),
                'today' => $vendor->notifications()->whereDate('created_at', today())->count(),
                'this_week' => $vendor->notifications()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'by_type' => $vendor->notifications()
                    ->reorder()
                    ->selectRaw('type, COUNT(*) as count, SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread_count')
                    ->groupBy('type')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $type = str_replace('App\\Notifications\\', '', $item->type);
                        return [strtolower($type) => [
                            'total' => (int) $item->count,
                            'unread' => (int) $item->unread_count,
                        ]];
                    }),
            ];

            return $this->successResponse([
                'stats' => $stats,
            ], 'message.success');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
