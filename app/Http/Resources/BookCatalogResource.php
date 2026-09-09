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
            'coverImageUrl' => $this->cover_image_url,
            // Selalu berupa array, bukan Collection. Hasil resource ini
            // ikut tersimpan di cache (BuildHomeCatalogSections); Collection
            // yang di-serialize berubah jadi __PHP_Incomplete_Class saat
            // di-unserialize sehingga penulis & kategori buku hilang.
            'authors' => $this->whenLoaded(
                'authors',
                fn (): array => $this->authors->pluck('name')->values()->all()
            ),
            'categories' => $this->whenLoaded(
                'categories',
                fn (): array => $this->categories
                    ->map(fn ($category): array => [
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ])
                    ->values()
                    ->all()
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
