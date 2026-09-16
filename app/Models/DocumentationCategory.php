<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\DocumentationCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentationCategory extends Model
{
    /** @use HasFactory<DocumentationCategoryFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function slugSourceAttribute(): string
    {
        return 'name';
    }

    protected static function slugFallbackValue(): string
    {
        return 'kategori-panduan';
    }

    /**
     * @return HasMany<Documentation, $this>
     */
    public function documentations(): HasMany
    {
        return $this->hasMany(Documentation::class)->orderBy('sort_order');
    }
}
