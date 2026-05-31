<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Aktivitas')
                    ->searchable()
                    ->wrap()
                    ->description(fn (ActivityLog $record): ?string => $record->subject_label),
                TextColumn::make('user.name')
                    ->label('Pelaku')
                    ->searchable()
                    ->placeholder('Sistem'),
                TextColumn::make('action')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Kode')
                    ->options(fn (): array => ActivityLog::query()
                        ->select('action')
                        ->distinct()
                        ->orderBy('action')
                        ->pluck('action', 'action')
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada aktivitas admin')
            ->emptyStateDescription('Riwayat perubahan penting akan tampil di sini.');
    }
}
