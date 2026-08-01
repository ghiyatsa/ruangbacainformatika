<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Books\BookResource;
use App\Filament\Resources\Loans\LoanResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\VisitLogs\VisitLogResource;
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
            ->pendingMemberApproval()
            ->count();

        return [
            Stat::make('Peminjaman Aktif', $activeLoans)
                ->description($overdueLoans > 0 ? "{$overdueLoans} terlambat" : 'Semua tepat waktu')
                ->descriptionColor($overdueLoans > 0 ? 'danger' : 'success')
                ->descriptionIcon($overdueLoans > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->color('primary')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->url(LoanResource::getUrl('index', ['filters' => ['active_borrowers' => ['isActive' => true]]])),

            Stat::make('Kunjungan Hari Ini', $todayVisitors)
                ->description(match ($visitorTrend) {
                    'increase' => "+{$visitorDiff} dari kemarin",
                    'decrease' => "{$visitorDiff} dari kemarin",
                    default => 'Sama seperti kemarin',
                })
                ->descriptionColor(match ($visitorTrend) {
                    'increase' => 'success',
                    'decrease' => 'danger',
                    default => 'gray',
                })
                ->descriptionIcon(match ($visitorTrend) {
                    'increase' => Heroicon::OutlinedArrowTrendingUp,
                    'decrease' => Heroicon::OutlinedArrowTrendingDown,
                    default => Heroicon::OutlinedMinus,
                })
                ->color('primary')
                ->icon(Heroicon::OutlinedUserGroup)
                ->url(VisitLogResource::getUrl('index', ['filters' => ['today' => ['isActive' => true]]])),

            Stat::make('Koleksi Buku', $totalBooks)
                ->description("{$availableItems}/{$totalItems} tersedia")
                ->descriptionColor('gray')
                ->descriptionIcon(Heroicon::OutlinedBookOpen)
                ->color('info')
                ->icon(Heroicon::OutlinedBookOpen)
                ->url(BookResource::getUrl('index')),

            Stat::make('Anggota Baru Bulan Ini', $newMembersThisMonth)
                ->description("{$newMembersThisMonth} pendaftaran bulan ini")
                ->descriptionColor($pendingApproval > 0 ? 'warning' : 'gray')
                ->descriptionIcon(Heroicon::OutlinedCalendarDays)
                ->color('info')
                ->icon(Heroicon::OutlinedUserPlus)
                ->url(UserResource::getUrl('index', [
                    'filters' => [
                        'registered_between' => [
                            'registered_from' => now()->startOfMonth()->toDateString(),
                            'registered_until' => now()->endOfMonth()->toDateString(),
                        ],
                    ],
                ])),
        ];
    }
}
