<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait GeneratesSlug
{
    abstract protected static function slugSourceAttribute(): string;

    protected static function slugAttribute(): string
    {
        return 'slug';
    }

    protected static function slugFallbackValue(): string
    {
        return 'item';
    }

    protected static function bootGeneratesSlug(): void
    {
        static::saving(function (Model $model): void {
            $sourceAttribute = static::slugSourceAttribute();
            $sourceValue = $model->getAttribute($sourceAttribute);

            if (! filled($sourceValue)) {
                return;
            }

            if (! static::shouldGenerateSlug($model)) {
                return;
            }

            $model->setAttribute(
                static::slugAttribute(),
                static::generateUniqueSlug(
                    (string) $sourceValue,
                    $model->exists ? $model->getKey() : null,
                ),
            );
        });
    }

    public static function generateSlugPreview(?string $value): string
    {
        return static::normalizeSlug($value ?? '');
    }

    public static function generateSlug(string $value): string
    {
        $slug = static::normalizeSlug($value);

        return $slug !== '' ? $slug : static::slugFallbackValue();
    }

    public static function generateUniqueSlug(string $value, mixed $ignoreId = null): string
    {
        $baseSlug = static::generateSlug($value);
        $slug = $baseSlug;
        $suffix = 2;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    protected static function shouldGenerateSlug(Model $model): bool
    {
        return $model->isDirty(static::slugSourceAttribute())
            || blank($model->getAttribute(static::slugAttribute()));
    }

    protected static function slugExists(string $slug, mixed $ignoreId = null): bool
    {
        $query = static::query()->where(static::slugAttribute(), $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }

    protected static function normalizeSlug(string $value): string
    {
        return Str::of($value)
            ->replace('+', ' plus ')
            ->replace('#', ' sharp ')
            ->slug('-')
            ->value();
    }
}
