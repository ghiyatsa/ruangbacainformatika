<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\LoanConsequenceService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class RestrictedBorrowersOverviewWidget extends StatsOverviewWidget
{
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

    protected function memberQuery(): Builder
    {
        return User::query()->whereHas('roles', fn (Builder $query): Builder => $query->where('name', 'member'));
    }

    protected function getStats(): array
    {
        $loanConsequenceService = app(LoanConsequenceService::class);
        $thresholdDays = $loanConsequenceService->lateReturnSuspendAfterDays();
        $cooldownDays = $loanConsequenceService->lateReturnCooldownDays();

        $restrictedMembers = (clone $this->memberQuery())
            ->borrowingRestricted()
            ->count();

        $activeOverdueMembers = (clone $this->memberQuery())
            ->activeOverdueBorrowers()
            ->count();

        $cooldownMembers = (clone $this->memberQuery())
            ->lateReturnCooldown()
            ->count();

        return [
            Stat::make('Sedang Dibatasi', $restrictedMembers)
                ->description($restrictedMembers > 0
                    ? 'Ada anggota yang belum bisa meminjam'
                    : 'Tidak ada pembatasan peminjaman aktif')
                ->descriptionIcon($restrictedMembers > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->color($restrictedMembers > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedNoSymbol)
                ->url($this->usersUrl([
                    'restricted_borrowers' => ['isActive' => true],
                ])),

            Stat::make('Terlambat Aktif', $activeOverdueMembers)
                ->description("Melewati batas keterlambatan {$thresholdDays} hari")
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color($activeOverdueMembers > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedClock)
                ->url($this->usersUrl([
                    'active_overdue_borrowers' => ['isActive' => true],
                ])),

            Stat::make('Masa Jeda', $cooldownMembers)
                ->description($cooldownDays > 0
                    ? "Masih dalam jeda {$cooldownDays} hari setelah terlambat"
                    : 'Masa jeda keterlambatan tidak aktif')
                ->descriptionIcon(Heroicon::OutlinedArrowPathRoundedSquare)
                ->color($cooldownMembers > 0 ? 'info' : 'gray')
                ->icon(Heroicon::OutlinedArrowPathRoundedSquare)
                ->url($this->usersUrl([
                    'late_return_cooldown' => ['isActive' => true],
                ])),
        ];
    }
}
