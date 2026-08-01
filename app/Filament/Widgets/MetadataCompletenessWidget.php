<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Books\BookResource;
use App\Models\Book;
use App\Support\MetadataCompleteness;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MetadataCompletenessWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = ['xl' => 2];

    protected function getStats(): array
    {
        $filledSql = '('.MetadataCompleteness::filledSql().')';
        $totalSql = MetadataCompleteness::total();

        $rows = Book::query()
            ->selectRaw("COUNT(*) AS total, {$filledSql} AS filled")
            ->groupBy('filled')
            ->get();

        $total = $rows->sum('total');
        $counts = [
            MetadataCompleteness::LEVEL_LENGKAP => $rows->where('filled', $totalSql)->sum('total'),
            MetadataCompleteness::LEVEL_SEBAGIAN => $rows
                ->where('filled', '>=', (int) round($totalSql / 2))
                ->where('filled', '<', $totalSql)
                ->sum('total'),
            MetadataCompleteness::LEVEL_KURANG => $rows
                ->where('filled', '<', (int) round($totalSql / 2))
                ->sum('total'),
        ];

        $percentage = $total > 0 ? (int) round($counts[MetadataCompleteness::LEVEL_LENGKAP] / $total * 100) : 0;

        return [
            Stat::make('Metadata Lengkap', $counts[MetadataCompleteness::LEVEL_LENGKAP])
                ->description("{$percentage}% dari {$total} buku")
                ->descriptionIcon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->icon(Heroicon::OutlinedDocumentCheck)
                ->url($this->booksUrl(MetadataCompleteness::LEVEL_LENGKAP)),
            Stat::make('Perlu Kurasi', $counts[MetadataCompleteness::LEVEL_SEBAGIAN])
                ->description('Beberapa elemen kurang')
                ->descriptionIcon(Heroicon::OutlinedDocumentMinus)
                ->color('warning')
                ->icon(Heroicon::OutlinedDocumentText)
                ->url($this->booksUrl(MetadataCompleteness::LEVEL_SEBAGIAN)),
            Stat::make('Tidak Lengkap', $counts[MetadataCompleteness::LEVEL_KURANG])
                ->description('Butuh kurasi segera')
                ->descriptionIcon(Heroicon::OutlinedDocumentArrowDown)
                ->color('danger')
                ->icon(Heroicon::OutlinedDocument)
                ->url($this->booksUrl(MetadataCompleteness::LEVEL_KURANG)),
        ];
    }

    protected function booksUrl(string $level): string
    {
        return BookResource::getUrl('index', [
            'filters' => [
                'metadata_level' => ['value' => $level],
            ],
        ]);
    }
}
