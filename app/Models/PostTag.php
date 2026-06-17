<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\PostTagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PostTag extends Model
{
    /** @use HasFactory<PostTagFactory> */
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
        return 'tag-artikel';
    }

    public static function findOrCreateByName(string $name): self
    {
        $normalizedName = trim($name);
        $slug = static::generateSlug($normalizedName);

        return static::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $normalizedName],
        );
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public function deletionBlockedReason(): ?string
    {
        $postsCount = $this->posts()->count();

        if ($postsCount > 0) {
            return "Tag ini masih dipakai pada {$postsCount} artikel.";
        }

        return null;
    }
}
