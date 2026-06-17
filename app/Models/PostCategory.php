<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\PostCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PostCategory extends Model
{
    /** @use HasFactory<PostCategoryFactory> */
    use GeneratesSlug;

    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected static function slugSourceAttribute(): string
    {
        return 'name';
    }

    protected static function slugFallbackValue(): string
    {
        return 'kategori-artikel';
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_category_post');
    }

    public function deletionBlockedReason(): ?string
    {
        $postsCount = $this->posts()->count();

        if ($postsCount > 0) {
            return "Kategori ini masih dipakai pada {$postsCount} artikel.";
        }

        return null;
    }
}
