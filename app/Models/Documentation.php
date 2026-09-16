<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\DocumentationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documentation extends Model
{
    /** @use HasFactory<DocumentationFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'documentation_category_id',
        'title',
        'slug',
        'summary',
        'content',
        'is_published',
        'sort_order',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function slugSourceAttribute(): string
    {
        return 'title';
    }

    protected static function slugFallbackValue(): string
    {
        return 'panduan';
    }

    /**
     * @return BelongsTo<DocumentationCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentationCategory::class, 'documentation_category_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @param  Builder<Documentation>  $query
     * @return Builder<Documentation>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
