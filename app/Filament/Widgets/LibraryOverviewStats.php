<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\BookItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class LibraryOverviewStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $booksCount = Book::query()->count();
        $availableItemsCount = BookItem::query()->where('status', 'available')->count();
        $draftBooksCount = Book::query()->where('is_published', false)->count();
        $booksWithoutCoverCount = Book::query()->whereNull('cover_image')->count();

        return [
            Stat::make('Total Buku', $booksCount)
                ->description('Seluruh metadata buku yang sudah tercatat.')
                ->descriptionIcon('heroicon-m-book-open')
                ->chart($this->getBooksTrend())
                ->color('primary'),
            Stat::make('Eksemplar Tersedia', $availableItemsCount)
                ->description('Eksemplar yang siap dipinjam saat ini.')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Buku Draf', $draftBooksCount)
                ->description('Buku yang belum dipublikasikan ke katalog.')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('warning'),
            Stat::make('Tanpa Cover', $booksWithoutCoverCount)
                ->description('Buku yang masih memakai cover default.')
                ->descriptionIcon('heroicon-m-photo')
                ->color('danger'),
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getBooksTrend(): array
    {
        $startDate = now()->subDays(6)->startOfDay();

        $counts = Book::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('aggregate', 'date');

        return collect(range(0, 6))
            ->map(function (int $offset) use ($counts, $startDate): int {
                $date = Carbon::parse($startDate)->addDays($offset)->toDateString();

                return (int) ($counts[$date] ?? 0);
            })
            ->all();
    }
}
