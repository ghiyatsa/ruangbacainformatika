<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostApprovedNotification extends Notification
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
            'kind' => 'post_approved',
            'title' => 'Artikel diterbitkan',
            'message' => sprintf('"%s" telah diterbitkan.', $this->post->title),
            'action_label' => 'Buka artikel',
            'action_url' => route('blog.show', $this->post, absolute: false),
            'icon' => 'book-check',
            'post_id' => $this->post->id,
            'post_slug' => $this->post->slug,
        ];
    }
}
