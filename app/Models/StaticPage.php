<?php

namespace App\Models;

use Database\Factories\StaticPageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{
    /** @use HasFactory<StaticPageFactory> */
    use HasFactory;

    protected $fillable = [
        'page_key',
        'title',
        'slug',
        'summary',
        'content',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isSystemPage(): bool
    {
        return filled($this->page_key);
    }

    public function publicPath(): string
    {
        return $this->isSystemPage()
            ? match ($this->page_key) {
                'about' => route('about', absolute: false),
                'privacy-policy' => route('privacy-policy', absolute: false),
                'terms-of-service' => route('terms-of-service', absolute: false),
                default => route('pages.show', ['slug' => $this->slug], absolute: false),
            }
        : route('pages.show', ['slug' => $this->slug], absolute: false);
    }

    public function publicUrl(): string
    {
        return url($this->publicPath());
    }
}
