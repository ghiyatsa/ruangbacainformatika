<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\User;
use App\Models\VisitLog;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class OperationsOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Ringkasan Operasional';

    protected ?string $description = 'Angka utama hari ini.';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $activeLoans = Loan::query()
            ->where('status', Loan::STATUS_BORROWED)
            ->count();

        $overdueLoans = Loan::query()
            ->where('status', Loan::STATUS_BORROWED)
            ->where('due_at', '<', now())
            ->count();

        $todayVisitors = VisitLog::query()
            ->whereDate('visited_at', today())
            ->count();

        $yesterdayVisitors = VisitLog::query()
            ->whereDate('visited_at', Carbon::yesterday())
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

        $newMembersThisMonth = User::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
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
