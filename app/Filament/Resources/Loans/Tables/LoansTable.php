<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Filament\Resources\Loans\LoanResource;
use App\Models\Loan;
use App\Models\User;
use App\Services\LoanReminderService;
use App\Support\AppTimezone;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama anggota atau email')
            ->emptyStateHeading('Belum ada data peminjaman')
            ->emptyStateDescription('Riwayat peminjaman akan muncul di sini.')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->recordUrl(fn (User $record): string => LoanResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label('Anggota')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): string => $record->email),
                TextColumn::make('active_loans_count')
                    ->label('Transaksi Aktif')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('total_active_items')
                    ->label('Item Belum Kembali')
                    ->badge()
                    ->state(fn (User $record): int => $record->loans->sum('active_items_count'))
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('total_books_borrowed')
                    ->label('Total Riwayat Buku')
                    ->state(fn (User $record): int => $record->loans->sum('items_count'))
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('loan_status')
                    ->label('Status')
                    ->options(Loan::statusOptions())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'],
                        fn (Builder $query, $value): Builder => $query->whereHas('loans', fn ($q) => $q->where('status', $value))
                    )),
                Filter::make('borrowed_between')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('borrowed_from')
                            ->label('Dari'),
                        DatePicker::make('borrowed_until')
                            ->label('Sampai'),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query->whereHas(
                            'loans',
                            fn (Builder $q): Builder => $q->when(
                                filled($data['borrowed_from'] ?? null),
                                function (Builder $sq) use ($data): Builder {
                                    [$startOfDay] = AppTimezone::dayRange($data['borrowed_from'] ?? null);

                                    return $sq->where('borrowed_at', '>=', $startOfDay);
                                }
                            )->when(
                                filled($data['borrowed_until'] ?? null),
                                function (Builder $sq) use ($data): Builder {
                                    [, $endOfDay] = AppTimezone::dayRange($data['borrowed_until'] ?? null);

                                    return $sq->where('borrowed_at', '<=', $endOfDay);
                                }
                            )
                        )
                    ),
                Filter::make('active_borrowers')
                    ->label('Hanya pinjaman aktif')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereHas('loans', fn ($q) => $q->where('status', Loan::STATUS_BORROWED))),
                Filter::make('overdue_borrowers')
                    ->label('Hanya terlambat')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereHas('loans', fn ($q) => $q
                        ->where('status', Loan::STATUS_BORROWED)
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', now()))),
                Filter::make('restricted_borrowers')
                    ->label('Hanya akun dibatasi')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->borrowingRestricted()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),
                Action::make('remindReturn')
                    ->label('Kirim Reminder')
                    ->icon(Heroicon::OutlinedBellAlert)
                    ->color('info')
                    ->visible(fn (User $record): bool => $record->active_loans_count > 0)
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Reminder Pengembalian')
                    ->modalDescription('Reminder akan dikirim ke WhatsApp dan notifikasi akun untuk pinjaman aktif yang jatuh tempo (H-1 s.d. telat maksimal 7 hari) dan belum diingatkan hari ini.')
                    ->action(function (User $record): void {
                        $sent = app(LoanReminderService::class)->remindAllActive($record);

                        Notification::make()
                            ->title($sent > 0 ? "Reminder dikirim untuk {$sent} pinjaman" : 'Tidak ada pinjaman yang perlu diingatkan')
                            ->{$sent > 0 ? 'success' : 'warning'}()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('remindReturnSelected')
                        ->label('Kirim Reminder Terpilih')
                        ->icon(Heroicon::OutlinedBellAlert)
                        ->color('info')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $total = 0;

                            foreach ($records as $record) {
                                if ($record instanceof User) {
                                    $total += app(LoanReminderService::class)->remindAllActive($record);
                                }
                            }

                            Notification::make()
                                ->title($total > 0 ? "Reminder dikirim untuk {$total} pinjaman" : 'Tidak ada pinjaman yang perlu diingatkan')
                                ->{$total > 0 ? 'success' : 'warning'}()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
