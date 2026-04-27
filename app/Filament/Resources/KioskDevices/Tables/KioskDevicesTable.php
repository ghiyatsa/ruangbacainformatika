<?php

namespace App\Filament\Resources\KioskDevices\Tables;

use App\Models\KioskDevice;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class KioskDevicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama perangkat, kiosk ID, kode registrasi, atau IP')
            ->emptyStateHeading('Belum ada perangkat kiosk')
            ->emptyStateDescription('Perangkat baru akan muncul otomatis saat wrapper kiosk mengakses sistem.')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50])
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Perangkat')
                    ->searchable()
                    ->placeholder('Belum diberi nama'),
                TextColumn::make('registration_code')
                    ->label('Kode Registrasi')
                    ->badge()
                    ->copyable(),
                TextColumn::make('kiosk_identifier')
                    ->label('Kiosk ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        KioskDevice::STATUS_APPROVED => 'Disetujui',
                        KioskDevice::STATUS_REJECTED => 'Ditolak',
                        KioskDevice::STATUS_REVOKED => 'Dicabut',
                        default => 'Menunggu',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        KioskDevice::STATUS_APPROVED => 'success',
                        KioskDevice::STATUS_REJECTED => 'danger',
                        KioskDevice::STATUS_REVOKED => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('connectivity')
                    ->label('Konektivitas')
                    ->state(fn (KioskDevice $record): string => $record->connectivityStatus())
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'online' => 'Online',
                        'stale' => 'Tidak Stabil',
                        default => 'Offline',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'success',
                        'stale' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(),
                TextColumn::make('last_seen_at')
                    ->label('Terakhir Terlihat')
                    ->since()
                    ->sortable(),
                IconColumn::make('isOnline')
                    ->label('Aktif')
                    ->boolean()
                    ->getStateUsing(fn (KioskDevice $record): bool => $record->isOnline())
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approved_at')
                    ->label('Disetujui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        KioskDevice::STATUS_PENDING => 'Menunggu',
                        KioskDevice::STATUS_APPROVED => 'Disetujui',
                        KioskDevice::STATUS_REJECTED => 'Ditolak',
                        KioskDevice::STATUS_REVOKED => 'Dicabut',
                    ]),
                Filter::make('online')
                    ->label('Online')
                    ->query(fn (Builder $query): Builder => $query->where('last_seen_at', '>=', now()->subMinutes(5))),
                Filter::make('offline')
                    ->label('Offline > 1 jam')
                    ->query(fn (Builder $query): Builder => $query
                        ->where(fn (Builder $deviceQuery): Builder => $deviceQuery
                            ->whereNull('last_seen_at')
                            ->orWhere('last_seen_at', '<', now()->subHour()))),
                Filter::make('pending_approval')
                    ->label('Menunggu Persetujuan')
                    ->query(fn (Builder $query): Builder => $query->where('status', KioskDevice::STATUS_PENDING)),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (KioskDevice $record): bool => ! $record->isApproved())
                    ->action(fn (KioskDevice $record) => $record->approve(Auth::id())),
                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (KioskDevice $record): bool => $record->isPending())
                    ->action(fn (KioskDevice $record) => $record->reject()),
                Action::make('revoke')
                    ->label('Cabut')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (KioskDevice $record): bool => $record->isApproved())
                    ->action(fn (KioskDevice $record) => $record->revoke()),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('Setujui Terpilih')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (KioskDevice $record) => $record->approve(Auth::id()))),
                    BulkAction::make('rejectSelected')
                        ->label('Tolak Terpilih')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->reject()),
                    BulkAction::make('revokeSelected')
                        ->label('Cabut Terpilih')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->revoke()),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('last_seen_at', 'desc');
    }
}
