<?php

namespace App\Http\Resources;

use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PostComment */
class PostCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'parentId' => $this->parent_id,
            'createdAt' => $this->created_at?->toIso8601String(),
            'createdAtLabel' => $this->created_at?->diffForHumans(),
            'user' => $this->relationLoaded('user') && $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatarUrl(),
                'initials' => $this->user->initials(),
            ] : null,
            'replyToCommentId' => $this->reply_to_comment_id,
            'replyToUser' => $this->relationLoaded('replyToComment') && $this->replyToComment?->relationLoaded('user') && $this->replyToComment->user ? [
                'id' => $this->replyToComment->user->id,
                'name' => $this->replyToComment->user->name,
            ] : null,
            'replies' => $this->whenLoaded('replies', fn () => PostCommentResource::collection($this->replies)->resolve()),
            'canDelete' => $request->user() ? $request->user()->can('delete', $this->resource) : false,
        ];
    }
}
