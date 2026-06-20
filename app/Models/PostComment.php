<?php

namespace App\Models;

use Database\Factories\PostCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostComment extends Model
{
    /** @use HasFactory<PostCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'reply_to_comment_id',
        'content',
    ];

    /**
     * Get the post that owns the comment.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment of this reply.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    /**
     * Get the specific comment being replied to.
     */
    public function replyToComment(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'reply_to_comment_id');
    }

    /**
     * Get the replies/child comments for this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(PostComment::class, 'parent_id')->latest();
    }
}
