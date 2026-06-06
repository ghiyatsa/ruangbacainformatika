<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\AppTimezone;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingMemberApprovalsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function usersUrl(array $filters = []): string
    {
        return UserResource::getUrl('index', [
            'filters' => $filters,
        ]);
    }

    protected function getStats(): array
    {
        [$todayStart, $todayEnd] = AppTimezone::dayRange();

        $pendingTotal = User::query()
            ->pendingMemberApproval()
            ->count();

        $pendingRegisteredToday = User::query()
            ->pendingMemberApproval()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        $approvedToday = User::query()
            ->where('is_approved', true)
            ->whereBetween('updated_at', [$todayStart, $todayEnd])
            ->count();

        return [
            Stat::make('Menunggu Persetujuan', $pendingTotal)
                ->description($pendingTotal > 0 ? 'Akun menunggu review admin' : 'Belum ada antrean')
                ->descriptionIcon($pendingTotal > 0 ? Heroicon::OutlinedClock : Heroicon::OutlinedCheckCircle)
                ->color($pendingTotal > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedUserPlus)
                ->url($this->usersUrl([
                    'is_approved' => ['value' => '0'],
                    'manual_approval' => ['isActive' => true],
                ])),

            Stat::make('Daftar Hari Ini', $pendingRegisteredToday)
                ->description($pendingRegisteredToday > 0 ? 'Pendaftaran baru yang masih menunggu review' : 'Belum ada pendaftaran baru')
                ->descriptionIcon(Heroicon::OutlinedAcademicCap)
                ->color($pendingRegisteredToday > 0 ? 'info' : 'gray')
                ->icon(Heroicon::OutlinedAcademicCap)
                ->url($this->usersUrl([
                    'manual_approval' => ['isActive' => true],
                    'registered_today' => ['isActive' => true],
                ])),

            Stat::make('Review Awal Hari Ini', $approvedToday)
                ->description($approvedToday > 0 ? 'Review awal bertambah hari ini' : 'Belum ada review awal baru')
                ->descriptionIcon(Heroicon::OutlinedCheckBadge)
                ->color($approvedToday > 0 ? 'success' : 'gray')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->url($this->usersUrl([
                    'approved_today' => ['isActive' => true],
                ])),
        ];
    }
}
