<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\User;
use App\Models\VisitLog;
use App\Support\AppTimezone;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Ringkasan Operasional';

    protected ?string $description = 'Angka utama hari ini.';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        [$todayStart, $todayEnd] = VisitLog::adminDayRange();
        [$yesterdayStart, $yesterdayEnd] = VisitLog::adminDayRange(
            now(VisitLog::adminTimezone())->subDay(),
        );

        $activeLoans = Loan::query()
            ->where('status', Loan::STATUS_BORROWED)
            ->count();

        $overdueLoans = Loan::query()
            ->where('status', Loan::STATUS_BORROWED)
            ->where('due_at', '<', now())
            ->count();

        $todayVisitors = VisitLog::query()
            ->whereBetween('visited_at', [$todayStart, $todayEnd])
            ->count();

        $yesterdayVisitors = VisitLog::query()
            ->whereBetween('visited_at', [$yesterdayStart, $yesterdayEnd])
            ->count();

        $visitorDiff = $todayVisitors - $yesterdayVisitors;
        $visitorTrend = match (true) {
            $visitorDiff > 0 => 'increase',
            $visitorDiff < 0 => 'decrease',
            default => 'flat',
        };

        $totalBooks = Book::query()->count();
        $availableItems = BookItem::query()->where('status', 'available')->count();
        $totalItems = BookItem::query()->count();
        [$monthStart, $monthEnd] = AppTimezone::monthRange();

        $newMembersThisMonth = User::query()
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $pendingApproval = User::query()
            ->where('is_approved', false)
            ->count();

        return [
            Stat::make('Peminjaman Aktif', $activeLoans)
                ->description($overdueLoans > 0 ? "{$overdueLoans} terlambat" : 'Semua aman')
                ->descriptionIcon($overdueLoans > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->color($overdueLoans > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedRectangleStack),

            Stat::make('Kunjungan Hari Ini', $todayVisitors)
                ->description(match ($visitorTrend) {
                    'increase' => "+{$visitorDiff} dari kemarin",
                    'decrease' => "{$visitorDiff} dari kemarin",
                    default => 'Sama dari kemarin',
                })
                ->descriptionIcon(match ($visitorTrend) {
                    'increase' => Heroicon::OutlinedArrowTrendingUp,
                    'decrease' => Heroicon::OutlinedArrowTrendingDown,
                    default => Heroicon::OutlinedMinus,
                })
                ->color(match ($visitorTrend) {
                    'increase' => 'success',
                    'decrease' => 'warning',
                    default => 'info',
                })
                ->icon(Heroicon::OutlinedUserGroup),

            Stat::make('Koleksi Buku', $totalBooks)
                ->description("{$availableItems}/{$totalItems} siap pinjam")
                ->descriptionIcon(Heroicon::OutlinedBookOpen)
                ->color('info')
                ->icon(Heroicon::OutlinedBookOpen),

            Stat::make('Anggota Baru Bulan Ini', $newMembersThisMonth)
                ->description($pendingApproval > 0 ? "{$pendingApproval} perlu review" : 'Semua aktif')
                ->descriptionIcon($pendingApproval > 0 ? Heroicon::OutlinedClock : Heroicon::OutlinedCheckBadge)
                ->color($pendingApproval > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedUserPlus),
        ];
    }
}
