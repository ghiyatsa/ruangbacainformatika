<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostRejectedNotification extends Notification
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
            'kind' => 'post_rejected',
            'title' => 'Artikel perlu perbaikan',
            'message' => $this->post->rejection_reason
                ? sprintf('"%s" memerlukan perbaikan. Catatan: %s', $this->post->title, $this->post->rejection_reason)
                : sprintf('"%s" memerlukan perbaikan sebelum diterbitkan.', $this->post->title),
            'action_label' => 'Buka editor',
            'action_url' => '/dashboard/posts/'.$this->post->getRouteKey().'/edit',
            'icon' => 'bell-ring',
            'post_id' => $this->post->id,
            'post_slug' => $this->post->slug,
            'rejection_reason' => $this->post->rejection_reason,
        ];
    }
}
