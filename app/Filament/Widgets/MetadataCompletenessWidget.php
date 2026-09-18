<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Books\BookResource;
use App\Models\Book;
use App\Support\MetadataCompleteness;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class MetadataCompletenessWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    /** Lama simpan ringkasan kelengkapan metadata. */
    protected const CACHE_SECONDS = 300;

    protected function getStats(): array
    {
        // Perhitungan ini memindai seluruh tabel buku dan menjalankan dua
        // subquery EXISTS per baris, sehingga mahal untuk dijalankan pada
        // setiap render. Metadata buku tidak berubah secepat itu, jadi
        // hasilnya disimpan sebentar.
        $summary = $this->summary();

        $total = $summary['total'];
        $counts = $summary['counts'];
        $percentage = $summary['percentage'];

        return [
            Stat::make('Metadata Lengkap', $counts[MetadataCompleteness::LEVEL_LENGKAP])
                ->description("{$percentage}% dari {$total} judul")
                ->descriptionIcon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->icon(Heroicon::OutlinedDocumentCheck)
                ->url($this->booksUrl(MetadataCompleteness::LEVEL_LENGKAP)),
            Stat::make('Perlu Dilengkapi', $counts[MetadataCompleteness::LEVEL_SEBAGIAN])
                ->color('warning')
                ->icon(Heroicon::OutlinedDocumentText)
                ->url($this->booksUrl(MetadataCompleteness::LEVEL_SEBAGIAN)),
            Stat::make('Data Minim', $counts[MetadataCompleteness::LEVEL_KURANG])
                ->color('danger')
                ->icon(Heroicon::OutlinedDocument)
                ->url($this->booksUrl(MetadataCompleteness::LEVEL_KURANG)),
        ];
    }

    /**
     * Ringkasan kelengkapan metadata, disimpan singkat di cache.
     *
     * @return array{total: int, counts: array<string, int>, percentage: int}
     */
    protected function summary(): array
    {
        return Cache::remember(
            'metadata-completeness:summary',
            now()->addSeconds(self::CACHE_SECONDS),
            fn (): array => $this->computeSummary(),
        );
    }

    /**
     * @return array{total: int, counts: array<string, int>, percentage: int}
     */
    protected function computeSummary(): array
    {
        $filledSql = '('.MetadataCompleteness::filledSql().')';
        $totalSql = MetadataCompleteness::total();

        $rows = Book::query()
            ->selectRaw("COUNT(*) AS total, {$filledSql} AS filled")
            ->groupBy('filled')
            ->get();

        $total = (int) $rows->sum('total');
        $counts = [
            MetadataCompleteness::LEVEL_LENGKAP => (int) $rows->where('filled', $totalSql)->sum('total'),
            MetadataCompleteness::LEVEL_SEBAGIAN => (int) $rows
                ->where('filled', '>=', (int) round($totalSql / 2))
                ->where('filled', '<', $totalSql)
                ->sum('total'),
            MetadataCompleteness::LEVEL_KURANG => (int) $rows
                ->where('filled', '<', (int) round($totalSql / 2))
                ->sum('total'),
        ];

        return [
            'total' => $total,
            'counts' => $counts,
            'percentage' => $total > 0 ? (int) round($counts[MetadataCompleteness::LEVEL_LENGKAP] / $total * 100) : 0,
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
