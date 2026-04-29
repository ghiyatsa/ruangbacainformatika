<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Models\Loan;
use App\Support\Library\LibraryResourceActionFactory;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari member, email, ISBN, kode eksemplar, atau status peminjaman')
            ->emptyStateHeading('Belum ada transaksi peminjaman')
            ->emptyStateDescription('Transaksi pinjam dari kiosk akan tampil di sini.')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->columns([
                TextColumn::make('borrowed_at')
                    ->label('Waktu Pinjam')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('book_isbns')
                    ->label('ISBN Buku')
                    ->state(fn (Loan $record): string => $record->items
                        ->pluck('bookItem.book.isbn')
                        ->filter()
                        ->unique()
                        ->implode(', '))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('items.bookItem.book', fn (Builder $bookQuery): Builder => $bookQuery
                            ->where('isbn', 'like', "%{$search}%"));
                    })
                    ->toggleable(),
                TextColumn::make('book_item_codes')
                    ->label('Kode Eksemplar')
                    ->state(fn (Loan $record): string => $record->items
                        ->pluck('bookItem.internal_code')
                        ->filter()
                        ->unique()
                        ->implode(', '))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('items.bookItem', fn (Builder $bookItemQuery): Builder => $bookItemQuery
                            ->where('internal_code', 'like', "%{$search}%"));
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('items_count')
                    ->label('Total Item')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('active_items_count')
                    ->label('Belum Kembali')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('loan_duration')
                    ->label('Durasi')
                    ->state(function (Loan $record): string {
                        $comparisonDate = $record->returned_at ?? now();

                        return $record->borrowed_at?->diffForHumans($comparisonDate, true) ?? '-';
                    })
                    ->badge()
                    ->color(function (Loan $record): string {
                        if ($record->status === Loan::STATUS_RETURNED) {
                            return 'success';
                        }

                        $borrowedAt = $record->borrowed_at;

                        if ($borrowedAt === null) {
                            return 'gray';
                        }

                        return match (true) {
                            $borrowedAt->lte(now()->subDays(14)) => 'danger',
                            $borrowedAt->lte(now()->subDays(7)) => 'warning',
                            default => 'primary',
                        };
                    })
                    ->toggleable(),
                TextColumn::make('due_at')
                    ->label('Jatuh Tempo')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Loan::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Loan::STATUS_BORROWED => 'warning',
                        Loan::STATUS_RETURNED => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('returned_at')
                    ->label('Selesai Pada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(Loan::statusOptions()),
                Filter::make('borrowed_between')
                    ->label('Rentang Tanggal Pinjam')
                    ->form([
                        DatePicker::make('borrowed_from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('borrowed_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['borrowed_from'] ?? null),
                                fn (Builder $query): Builder => $query->where('borrowed_at', '>=', Carbon::parse($data['borrowed_from'])->startOfDay()),
                            )
                            ->when(
                                filled($data['borrowed_until'] ?? null),
                                fn (Builder $query): Builder => $query->where('borrowed_at', '<=', Carbon::parse($data['borrowed_until'])->endOfDay()),
                            );
                    }),
                Filter::make('active')
                    ->label('Masih Dipinjam')
                    ->query(fn (Builder $query): Builder => $query->where('status', Loan::STATUS_BORROWED)),
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('borrowed_at', today())),
                Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('borrowed_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ])),
                Filter::make('overdue_7_days')
                    ->label('Melewati Jatuh Tempo')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', Loan::STATUS_BORROWED)
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', now())),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat Detail'),
                    LibraryResourceActionFactory::deleteAction(
                        singularLabel: 'Transaksi Peminjaman',
                        fallbackReason: 'Transaksi peminjaman tersebut tidak dapat dihapus karena masih memiliki keterkaitan data.',
                        modalDescription: 'Transaksi peminjaman hanya dapat dihapus apabila seluruh item telah dikembalikan dan transaksi tidak lagi aktif.',
                    ),
                ])
                    ->label('Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    LibraryResourceActionFactory::deleteBulkAction(
                        singularLabel: 'transaksi peminjaman',
                        pluralLabel: 'transaksi peminjaman',
                        genericFailureReason: 'transaksi masih aktif atau masih memiliki item yang belum dikembalikan',
                    ),
                ]),
            ])
            ->defaultSort('borrowed_at', 'desc');
    }
}
