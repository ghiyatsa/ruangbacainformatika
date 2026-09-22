<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Dikirim saat perubahan artikel yang menunggu tinjauan disetujui.
 */
class PostRevisionApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Post $post,
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
            'kind' => 'post_revision_approved',
            'title' => 'Perubahan artikel diterbitkan',
            'message' => sprintf('Perubahan pada "%s" telah disetujui dan diterbitkan.', $this->post->title),
            'action_label' => 'Buka artikel',
            'action_url' => route('posts.show', $this->post, absolute: false),
            'icon' => 'book-check',
            'post_id' => $this->post->id,
            'post_slug' => $this->post->slug,
        ];
    }
}
