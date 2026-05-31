<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Support\AppTimezone;
use App\Support\LoanConsequenceService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama, email, WhatsApp, atau alamat')
            ->emptyStateHeading('Belum ada pengguna')
            ->emptyStateDescription('Data pengguna akan tampil di sini.')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('WhatsApp berhasil disalin'),
                TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('roles.name')
                    ->badge()
                    ->label('Peran')
                    ->separator(', ')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('borrowing_access')
                    ->label('Status Pinjam')
                    ->state(fn (User $record): string => app(LoanConsequenceService::class)->borrowingAccessSummary($record)['label'])
                    ->badge()
                    ->color(fn (User $record): string => app(LoanConsequenceService::class)->borrowingAccessSummary($record)['color'])
                    ->description(fn (User $record): ?string => app(LoanConsequenceService::class)->borrowingAccessSummary($record)['detail'])
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Persetujuan')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah disetujui')
                    ->falseLabel('Belum disetujui'),
                Filter::make('registered_between')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('registered_from')
                            ->label('Dari'),
                        DatePicker::make('registered_until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['registered_from'] ?? null),
                                function (Builder $query) use ($data): Builder {
                                    [$startOfDay] = AppTimezone::dayRange($data['registered_from'] ?? null);

                                    return $query->where('created_at', '>=', $startOfDay);
                                },
                            )
                            ->when(
                                filled($data['registered_until'] ?? null),
                                function (Builder $query) use ($data): Builder {
                                    [, $endOfDay] = AppTimezone::dayRange($data['registered_until'] ?? null);

                                    return $query->where('created_at', '<=', $endOfDay);
                                },
                            );
                    }),
                Filter::make('restricted_borrowers')
                    ->label('Hanya akun dibatasi')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->borrowingRestricted()),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->hidden(fn (User $record): bool => $record->is_approved)
                    ->requiresConfirmation()
                    ->modalHeading('Setujui akun pengguna')
                    ->modalDescription('Akun ini akan ditandai siap digunakan untuk layanan anggota.')
                    ->action(function (User $record): void {
                        $record->forceFill([
                            'is_approved' => true,
                        ])->save();

                        Notification::make()
                            ->success()
                            ->title('Akun berhasil disetujui')
                            ->send();
                    }),
                EditAction::make()
                    ->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('Setujui Terpilih')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records): void {
                            $approvedCount = 0;

                            foreach ($records as $record) {
                                if (! $record instanceof User || $record->is_approved) {
                                    continue;
                                }

                                $record->forceFill([
                                    'is_approved' => true,
                                ])->save();

                                $approvedCount++;
                            }

                            Notification::make()
                                ->success()
                                ->title($approvedCount > 0 ? "{$approvedCount} akun disetujui" : 'Tidak ada akun yang perlu disetujui')
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
