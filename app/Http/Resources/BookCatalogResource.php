<?php

namespace App\Http\Resources;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin Book */
class BookCatalogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'shortDescription' => Str::limit(
                $this->description ?: 'Deskripsi buku belum tersedia.',
                160
            ),
            'coverImageUrl' => $this->cover_image
                ? asset('storage/'.$this->cover_image)
                : asset('images/book-cover-placeholder.svg'),
            'authors' => $this->whenLoaded(
                'authors',
                fn () => $this->authors->pluck('name')->values()
            ),
            'categories' => $this->whenLoaded(
                'categories',
                fn () => $this->categories
                    ->map(fn ($category) => [
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ])
                    ->values()
            ),
            'publishedYear' => $this->published_year,
            'pages' => $this->pages,
            'availableItemsCount' => $this->is_borrowable
                ? ($this->available_items_count ?? 0)
                : 0,
            'isFeatured' => $this->is_featured,
            'isBorrowable' => $this->is_borrowable,
            'isAvailable' => $this->is_borrowable && ($this->available_items_count ?? 0) > 0,
            'viewCount' => $this->view_count,
        ];
    }
}
