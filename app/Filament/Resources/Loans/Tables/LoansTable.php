<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Filament\Resources\Loans\LoanResource;
use App\Models\Loan;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama anggota atau email')
            ->emptyStateHeading('Belum ada data peminjaman')
            ->emptyStateDescription('Riwayat peminjaman anggota akan tampil di sini.')
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
                                fn (Builder $sq): Builder => $sq->whereDate('borrowed_at', '>=', $data['borrowed_from'])
                            )->when(
                                filled($data['borrowed_until'] ?? null),
                                fn (Builder $sq): Builder => $sq->whereDate('borrowed_at', '<=', $data['borrowed_until'])
                            )
                        )
                    ),
                Filter::make('active_borrowers')
                    ->label('Hanya pinjaman aktif')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereHas('loans', fn ($q) => $q->where('status', Loan::STATUS_BORROWED))),
                Filter::make('overdue_borrowers')
                    ->label('Hanya lewat jatuh tempo')
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
                    ->label('Lihat Detail'),
            ])
            ->toolbarActions([])
            ->defaultSort('name', 'asc');
    }
}
