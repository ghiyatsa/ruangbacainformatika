<?php

namespace App\Observers;

use App\Models\Post;
use App\Notifications\PostApprovedNotification;
use App\Notifications\PostRejectedNotification;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PostObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(Post $post): void
    {
        if (! $post->wasChanged('status')) {
            return;
        }

        $post->loadMissing('user');

        if ($post->user === null) {
            return;
        }

        if ($post->status === Post::STATUS_APPROVED) {
            $post->user->notifyNow(new PostApprovedNotification($post));

            return;
        }

        if ($post->status === Post::STATUS_REJECTED) {
            $post->user->notifyNow(new PostRejectedNotification($post));
        }
    }
}
