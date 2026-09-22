<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Dikirim saat perubahan artikel yang menunggu tinjauan dikembalikan.
 */
class PostRevisionRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Post $post,
        protected string $reason,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'post_revision_rejected',
            'title' => 'Perubahan artikel perlu perbaikan',
            'message' => sprintf(
                'Perubahan pada "%s" perlu diperbaiki. Catatan: %s',
                $this->post->title,
                $this->reason,
            ),
            'action_label' => 'Buka editor',
            'action_url' => '/dashboard/posts/'.$this->post->getRouteKey().'/edit',
            'icon' => 'bell-ring',
            'post_id' => $this->post->id,
            'post_slug' => $this->post->slug,
            'rejection_reason' => $this->reason,
        ];
    }
}
