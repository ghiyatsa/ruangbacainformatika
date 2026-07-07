<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostCommentRequest;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PostCommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     */
    public function store(StorePostCommentRequest $request, Post $post): RedirectResponse
    {
        abort_unless($post->status === Post::STATUS_APPROVED, 404);

        if (! $post->allow_comments) {
            abort(403, 'Komentar dinonaktifkan untuk artikel ini.');
        }

        $validated = $request->validated();

        $parentId = $validated['parent_id'] ?? null;
        $replyToCommentId = null;

        if ($parentId !== null) {
            $parent = PostComment::where('id', $parentId)
                ->where('post_id', $post->id)
                ->first();

            if (! $parent) {
                throw ValidationException::withMessages([
                    'content' => 'Komentar induk tidak valid.',
                ]);
            }

            $replyTargetId = $validated['reply_to_comment_id'] ?? $parent->id;
            $replyTarget = PostComment::where('id', $replyTargetId)
                ->where('post_id', $post->id)
                ->first();

            if (! $replyTarget) {
                throw ValidationException::withMessages([
                    'content' => 'Komentar tujuan balasan tidak valid.',
                ]);
            }

            $threadRootId = $parent->parent_id ?? $parent->id;

            if ($replyTarget->id !== $threadRootId && $replyTarget->parent_id !== $threadRootId) {
                throw ValidationException::withMessages([
                    'content' => 'Komentar tujuan balasan tidak valid.',
                ]);
            }

            $parentId = $threadRootId;
            $replyToCommentId = $replyTarget->id;
        }

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'parent_id' => $parentId,
            'reply_to_comment_id' => $replyToCommentId,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Komentar Anda berhasil dikirim.',
        ]);

        return back(status: 303);
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(PostComment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return back();
    }
}
