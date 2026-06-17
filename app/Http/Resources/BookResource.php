<?php

namespace App\Http\Resources;

use App\Models\Book;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** @mixin Book */
class BookResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $displayShelfData = $this->displayShelfData();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'slug' => $this->slug,
            'isbn' => $this->isbn,
            'issn' => $this->issn,
            'description' => $this->description ?: 'Deskripsi buku belum tersedia.',
            'shortDescription' => Str::limit($this->description ?: 'Deskripsi buku belum tersedia.', 160),
            'coverImageUrl' => $this->cover_image
                ? asset('storage/'.$this->cover_image)
                : asset('images/book-cover-placeholder.svg'),
            'authors' => $this->whenLoaded('authors', fn () => $this->authors->pluck('name')->values()),
            'authorsData' => $this->whenLoaded('authors', fn () => $this->authors->map(fn ($author) => [
                'name' => $author->name,
                'slug' => $author->slug,
            ])->values()),
            'categories' => $this->whenLoaded('categories', fn () => CategoryResource::collection($this->categories)->resolve()),
            'publisher' => $this->whenLoaded('publisher', fn () => $this->publisher->name),
            'publisherData' => $this->whenLoaded('publisher', fn () => $this->publisher ? [
                'name' => $this->publisher->name,
                'slug' => $this->publisher->slug,
            ] : null),
            'publishedYear' => $this->published_year,
            'edition' => $this->edition,
            'pages' => $this->pages,
            'language' => $this->language,
            'itemsCount' => $this->items_count ?? 0,
            'availableItemsCount' => $this->is_borrowable ? ($this->available_items_count ?? 0) : 0,
            'isFeatured' => $this->is_featured,
            'isBorrowable' => $this->is_borrowable,
            'isAvailable' => $this->is_borrowable && ($this->available_items_count ?? 0) > 0,
            'viewCount' => $this->view_count,
            'displayShelfLocations' => $displayShelfData['locations'],
            'usesBackupShelfLocations' => $displayShelfData['usesBackup'],
        ];
    }

    /**
     * @return array{locations: list<string>, usesBackup: bool}
     */
    protected function displayShelfData(): array
    {
        if (! $this->relationLoaded('items')) {
            return [
                'locations' => [],
                'usesBackup' => false,
            ];
        }

        /** @var Collection<int, BookItem> $items */
        $items = $this->items
            ->sortBy('id')
            ->values();

        if ($items->isEmpty()) {
            return [
                'locations' => [],
                'usesBackup' => false,
            ];
        }

        $primaryDisplayItems = $items->take(5)->values();
        $primaryAvailableItems = $primaryDisplayItems
            ->where('status', 'available')
            ->values();

        if ($primaryAvailableItems->isNotEmpty()) {
            return [
                'locations' => $this->extractShelfLocations($primaryAvailableItems),
                'usesBackup' => false,
            ];
        }

        $backupAvailableItems = $items
            ->slice(5)
            ->where('status', 'available')
            ->values();

        if ($backupAvailableItems->isNotEmpty()) {
            return [
                'locations' => $this->extractShelfLocations($backupAvailableItems),
                'usesBackup' => true,
            ];
        }

        return [
            'locations' => $this->extractShelfLocations($primaryDisplayItems),
            'usesBackup' => false,
        ];
    }

    /**
     * @param  Collection<int, BookItem>  $items
     * @return list<string>
     */
    protected function extractShelfLocations(Collection $items): array
    {
        return $items
            ->pluck('shelf_location')
            ->filter(fn (?string $location): bool => filled($location))
            ->map(fn (string $location): string => trim($location))
            ->unique()
            ->values()
            ->all();
    }
}
