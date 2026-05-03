<?php

namespace App\Http\Resources;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin Book */
class BookResource extends JsonResource
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
            'isbn' => $this->isbn,
            'issn' => $this->issn,
            'description' => $this->description ?: 'Deskripsi buku belum tersedia.',
            'shortDescription' => Str::limit($this->description ?: 'Deskripsi buku belum tersedia.', 160),
            'coverImageUrl' => $this->cover_image
                ? asset('storage/'.$this->cover_image)
                : asset('images/book-cover-placeholder.svg'),
            'authors' => $this->whenLoaded('authors', fn () => $this->authors->pluck('name')),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->pluck('name')),
            'publisher' => $this->whenLoaded('publisher', fn () => $this->publisher->name),
            'publishedYear' => $this->published_year,
            'pages' => $this->pages,
            'language' => $this->language,
            'itemsCount' => $this->items_count ?? 0,
            'availableItemsCount' => $this->is_borrowable ? ($this->available_items_count ?? 0) : 0,
            'isFeatured' => $this->is_featured,
            'isBorrowable' => $this->is_borrowable,
            'isAvailable' => $this->is_borrowable && ($this->available_items_count ?? 0) > 0,
            'viewCount' => $this->view_count,
        ];

    }
}
