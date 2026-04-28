<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function adminIndex(Request $request): JsonResponse
    {
        $actor = $request->user();

        abort_unless($actor && in_array($actor->role, ['admin', 'manager'], true), 403, 'Hanya admin/manager yang dapat melihat notifikasi admin.');

        $perPage = min(max((int) $request->integer('per_page', 20), 5), 100);

        $query = AppNotification::query()
            ->where('recipient_user_id', $actor->id)
            ->latest();

        if ($request->filled('unread')) {
            $unread = filter_var($request->input('unread'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($unread === true) {
                $query->whereNull('read_at');
            }

            if ($unread === false) {
                $query->whereNotNull('read_at');
            }
        }

        return response()->json($query->paginate($perPage));
    }

    public function customerIndex(Request $request): JsonResponse
    {
        $actor = $request->user();

        abort_unless($actor && $actor->role === 'customer', 403, 'Hanya customer yang dapat melihat notifikasi customer.');

        $perPage = min(max((int) $request->integer('per_page', 20), 5), 100);

        $query = AppNotification::query()
            ->where('recipient_user_id', $actor->id)
            ->latest();

        if ($request->filled('unread')) {
            $unread = filter_var($request->input('unread'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($unread === true) {
                $query->whereNull('read_at');
            }

            if ($unread === false) {
                $query->whereNotNull('read_at');
            }
        }

        return response()->json($query->paginate($perPage));
    }

    public function markAsRead(Request $request, AppNotification $notification): JsonResponse
    {
        $actor = $request->user();

        abort_unless($actor && (int) $notification->recipient_user_id === (int) $actor->id, 403, 'Notifikasi ini bukan milik Anda.');

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Notifikasi ditandai sudah dibaca.',
            'data' => $notification->fresh(),
        ]);
    }

    public function pushReady(Request $request): JsonResponse
    {
        $actor = $request->user();

        abort_unless($actor, 401);

        $latestNotifications = AppNotification::query()
            ->where('recipient_user_id', $actor->id)
            ->latest('id')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'badge_count' => AppNotification::query()
                    ->where('recipient_user_id', $actor->id)
                    ->whereNull('read_at')
                    ->count(),
                'latest_notifications' => $latestNotifications->map(function (AppNotification $notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'priority' => $notification->priority,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at,
                    ];
                })->values(),
            ],
        ]);
    }
}
