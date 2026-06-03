<?php

namespace App\Http\Controllers;

use App\Models\User;
use Filament\Notifications\DatabaseNotification as FilamentDatabaseNotification;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        $notifications = $this->visibleNotifications($user)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => $this->serializeNotification($notification))
            ->values()
            ->all();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $this->visibleUnreadNotifications($user)->count(),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        $this->visibleUnreadNotifications($user)->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'unreadCount' => 0,
        ]);
    }

    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        $databaseNotification = $this->visibleNotifications($user)
            ->whereKey($notification)
            ->firstOrFail();

        if ($databaseNotification->read_at === null) {
            $databaseNotification->forceFill([
                'read_at' => now(),
            ])->save();
        }

        return response()->json([
            'unreadCount' => $this->visibleUnreadNotifications($user)->count(),
        ]);
    }

    protected function visibleUnreadNotifications(User $user): MorphMany
    {
        return $this->visibleNotifications($user)->whereNull('read_at');
    }

    protected function visibleNotifications(User $user): MorphMany
    {
        return $user->notifications()
            ->where('type', '!=', FilamentDatabaseNotification::class);
    }

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     message: string,
     *     actionLabel: string|null,
     *     actionUrl: string|null,
     *     icon: string|null,
     *     kind: string|null,
     *     readAt: string|null,
     *     createdAt: string
     * }
     */
    protected function serializeNotification(DatabaseNotification $notification): array
    {
        /** @var array<string, mixed> $data */
        $data = $notification->data;

        return [
            'id' => $notification->getKey(),
            'title' => (string) ($data['title'] ?? 'Notifikasi baru'),
            'message' => (string) ($data['message'] ?? 'Ada pembaruan pada akun Anda.'),
            'actionLabel' => isset($data['action_label']) ? (string) $data['action_label'] : null,
            'actionUrl' => isset($data['action_url']) ? (string) $data['action_url'] : null,
            'icon' => isset($data['icon']) ? (string) $data['icon'] : null,
            'kind' => isset($data['kind']) ? (string) $data['kind'] : null,
            'readAt' => $notification->read_at?->toIso8601String(),
            'createdAt' => $notification->created_at->toIso8601String(),
        ];
    }
}
