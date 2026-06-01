<?php

namespace App\Services;

use App\Models\Book;
use App\Support\BookItemCodeGenerator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookItemBatchCreator
{
    public function create(Book $book, int $quantity, ?string $shelfLocation = null): Collection
    {
        if ($quantity < 1) {
            return new Collection;
        }

        return DB::transaction(function () use ($book, $quantity, $shelfLocation): Collection {
            $nextSequence = $this->getNextSequence($book);
            $items = new Collection;
            $resolvedShelfLocation = $this->normalizeShelfLocation($shelfLocation);

            for ($offset = 0; $offset < $quantity; $offset++) {
                $sequence = $nextSequence + $offset;

                $items->push($book->items()->create([
                    'internal_code' => BookItemCodeGenerator::generate($book, $sequence),
                    'status' => 'available',
                    'condition' => 'good',
                    'shelf_location' => $resolvedShelfLocation,
                    'acquired_date' => now()->toDateString(),
                ]));
            }

            return $items;
        });
    }

    public function ensureStock(Book $book, int $desiredStock, ?string $shelfLocation = null): Collection
    {
        $currentStock = $book->items()->count();
        $missingStock = max($desiredStock - $currentStock, 0);

        return $this->create($book, $missingStock, $shelfLocation);
    }

    public function fillMissingShelfLocations(Book $book, string $shelfLocation): int
    {
        $resolvedShelfLocation = $this->normalizeShelfLocation($shelfLocation);

        return $book->items()
            ->where(function ($query): void {
                $query->whereNull('shelf_location')
                    ->orWhere('shelf_location', '')
                    ->orWhere('shelf_location', '-');
            })
            ->update([
                'shelf_location' => $resolvedShelfLocation,
            ]);
    }

    protected function getNextSequence(Book $book): int
    {
        $latestSequence = $book->items()
            ->pluck('internal_code')
            ->map(fn (string $internalCode): int => (int) str($internalCode)->afterLast('-')->toString())
            ->max();

        return (int) $latestSequence + 1;
    }

    protected function normalizeShelfLocation(?string $shelfLocation): string
    {
        $resolvedShelfLocation = trim((string) $shelfLocation);

        return $resolvedShelfLocation !== '' ? $resolvedShelfLocation : '-';
    }
}
