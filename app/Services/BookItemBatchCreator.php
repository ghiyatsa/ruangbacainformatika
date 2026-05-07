<?php

namespace App\Services;

use App\Models\Book;
use App\Support\BookItemCodeGenerator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookItemBatchCreator
{
    public function create(Book $book, int $quantity): Collection
    {
        if ($quantity < 1) {
            return new Collection;
        }

        return DB::transaction(function () use ($book, $quantity): Collection {
            $nextSequence = $this->getNextSequence($book);
            $items = new Collection;

            for ($offset = 0; $offset < $quantity; $offset++) {
                $sequence = $nextSequence + $offset;

                $items->push($book->items()->create([
                    'internal_code' => BookItemCodeGenerator::generate($book, $sequence),
                    'status' => 'available',
                    'condition' => 'good',
                    'shelf_location' => '-',
                    'acquired_date' => now()->toDateString(),
                ]));
            }

            return $items;
        });
    }

    public function ensureStock(Book $book, int $desiredStock): Collection
    {
        $currentStock = $book->items()->count();
        $missingStock = max($desiredStock - $currentStock, 0);

        return $this->create($book, $missingStock);
    }

    protected function getNextSequence(Book $book): int
    {
        $latestSequence = $book->items()
            ->pluck('internal_code')
            ->map(fn (string $internalCode): int => (int) str($internalCode)->afterLast('-')->toString())
            ->max();

        return (int) $latestSequence + 1;
    }
}
