<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\VisitLogs\VisitLogResource;
use App\Models\VisitLog;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayVisitorsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|array|null $columns = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        [$todayStart, $todayEnd] = VisitLog::adminDayRange();

        $total = VisitLog::query()->whereBetween('visited_at', [$todayStart, $todayEnd])->count();

        $byType = VisitLog::query()
            ->whereBetween('visited_at', [$todayStart, $todayEnd])
            ->selectRaw('visitor_type, COUNT(*) as total')
            ->groupBy('visitor_type')
            ->pluck('total', 'visitor_type');

        $mahasiswa = (int) ($byType[VisitLog::VISITOR_TYPE_MAHASISWA] ?? 0);
        $dosen = (int) ($byType[VisitLog::VISITOR_TYPE_DOSEN] ?? 0);
        $staff = (int) ($byType[VisitLog::VISITOR_TYPE_STAFF] ?? 0);
        $umum = (int) ($byType[VisitLog::VISITOR_TYPE_UMUM] ?? 0);

        $byPurpose = VisitLog::query()
            ->whereBetween('visited_at', [$todayStart, $todayEnd])
            ->selectRaw('purpose, COUNT(*) as total')
            ->groupBy('purpose')
            ->orderByDesc('total')
            ->pluck('total', 'purpose');

        $topPurposeKey = $byPurpose->keys()->first();
        $topPurposeLabel = $topPurposeKey
            ? (VisitLog::purposeOptions()[$topPurposeKey] ?? $topPurposeKey)
            : 'Belum ada kunjungan';

        return [
            Stat::make('Total Hari Ini', $total)
                ->description("Tujuan utama: {$topPurposeLabel}")
                ->descriptionColor('gray')
                ->descriptionIcon(Heroicon::OutlinedMapPin)
                ->color('primary')
                ->icon(Heroicon::OutlinedUserGroup)
                ->url(VisitLogResource::getUrl('index', ['filters' => ['today' => ['isActive' => true]]])),

            Stat::make('Mahasiswa', $mahasiswa)
                ->description("{$dosen} dosen hari ini")
                ->descriptionColor('gray')
                ->descriptionIcon(Heroicon::OutlinedAcademicCap)
                ->color('info')
                ->icon(Heroicon::OutlinedAcademicCap)
                ->url(VisitLogResource::getUrl('index', [
                    'filters' => [
                        'today' => ['isActive' => true],
                        'visitor_type' => ['value' => VisitLog::VISITOR_TYPE_MAHASISWA],
                    ],
                ])),

            Stat::make('Staf dan Umum', $staff + $umum)
                ->description("{$staff} staf, {$umum} umum")
                ->descriptionColor('gray')
                ->descriptionIcon(Heroicon::OutlinedBriefcase)
                ->color('info')
                ->icon(Heroicon::OutlinedBriefcase)
                ->url(VisitLogResource::getUrl('index', ['filters' => ['today' => ['isActive' => true]]])),

        ];
    }
}
