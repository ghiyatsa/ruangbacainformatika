<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Support\AppTimezone;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingMemberApprovalsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Review Anggota';

    protected ?string $description = 'Pantau antrean akun hasil klaim kiosk.';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        [$todayStart, $todayEnd] = AppTimezone::dayRange();

        $pendingTotal = User::query()
            ->where('is_approved', false)
            ->count();

        $pendingGoogleMembers = User::query()
            ->where('is_approved', false)
            ->where('auth_provider', 'google')
            ->count();

        $pendingStudents = User::query()
            ->where('is_approved', false)
            ->where('email', 'like', '%@mhs.unimal.ac.id')
            ->count();

        $approvedToday = User::query()
            ->where('is_approved', true)
            ->whereBetween('updated_at', [$todayStart, $todayEnd])
            ->count();

        return [
            Stat::make('Menunggu Review', $pendingTotal)
                ->description($pendingGoogleMembers > 0 ? "{$pendingGoogleMembers} hasil klaim Google" : 'Belum ada antrean baru')
                ->descriptionIcon($pendingGoogleMembers > 0 ? Heroicon::OutlinedClock : Heroicon::OutlinedCheckCircle)
                ->color($pendingTotal > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedUserPlus),

            Stat::make('Mahasiswa Pending', $pendingStudents)
                ->description('Prioritaskan akun mahasiswa yang masih menunggu review')
                ->descriptionIcon(Heroicon::OutlinedAcademicCap)
                ->color($pendingStudents > 0 ? 'info' : 'gray')
                ->icon(Heroicon::OutlinedAcademicCap),

            Stat::make('Disetujui Hari Ini', $approvedToday)
                ->description($approvedToday > 0 ? 'Ada progres review' : 'Belum ada approval hari ini')
                ->descriptionIcon(Heroicon::OutlinedCheckBadge)
                ->color($approvedToday > 0 ? 'success' : 'gray')
                ->icon(Heroicon::OutlinedCheckBadge),
        ];
    }
}
