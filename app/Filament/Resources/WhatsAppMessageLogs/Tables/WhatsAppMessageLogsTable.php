<?php

namespace App\Filament\Resources\WhatsAppMessageLogs\Tables;

use App\Models\WhatsAppMessageLog;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsAppMessageLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('phone_number_masked')
                    ->label('Nomor'),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('notification_type')
                    ->label('Tipe')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (WhatsAppMessageLog $record): string => match ($record->status) {
                        WhatsAppMessageLog::StatusSent => 'success',
                        WhatsAppMessageLog::StatusPending => 'warning',
                        WhatsAppMessageLog::StatusFailed => 'danger',
                        WhatsAppMessageLog::StatusSkipped => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('attempts')
                    ->label('Percobaan')
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        WhatsAppMessageLog::StatusPending => 'Pending',
                        WhatsAppMessageLog::StatusSent => 'Sent',
                        WhatsAppMessageLog::StatusFailed => 'Failed',
                        WhatsAppMessageLog::StatusSkipped => 'Skipped',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(fn (): array => WhatsAppMessageLog::query()
                        ->select('category')
                        ->distinct()
                        ->pluck('category', 'category')
                        ->filter()
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada log pesan WhatsApp')
            ->emptyStateDescription('Log OTP dan pesan WhatsApp akan muncul di sini.');
    }
}
