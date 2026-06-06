<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Loans\LoanResource;
use App\Models\Loan;
use App\Models\LoanItem;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;

class OverdueLoanTableWidget extends BaseTableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Loan::query()
                    ->with(['user', 'items.bookItem.book'])
                    ->where('status', Loan::STATUS_BORROWED)
                    ->where('due_at', '<', now())
                    ->latest('due_at')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Anggota')
                    ->searchable()
                    ->icon(Heroicon::OutlinedUser)
                    ->iconPosition('before'),

                TextColumn::make('items_count')
                    ->label('Jumlah Buku')
                    ->state(fn (Loan $record): int => $record->items->count())
                    ->icon(Heroicon::OutlinedBookOpen)
                    ->iconPosition('before'),

                TextColumn::make('borrowed_at')
                    ->label('Dipinjam')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('due_at')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('overdue_days')
                    ->label('Terlambat')
                    ->state(fn (Loan $record): int => abs((int) now()->diffInDays($record->due_at)))
                    ->suffix(' hari')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('items_titles')
                    ->label('Buku')
                    ->state(function (Loan $record): string {
                        return $record->items
                            ->map(fn (LoanItem $item): string => $item->bookItem?->book?->title ?? '-')
                            ->filter()
                            ->join(', ');
                    })
                    ->limit(60)
                    ->tooltip(function (Loan $record): string {
                        return $record->items
                            ->map(fn (LoanItem $item): string => $item->bookItem?->book?->title ?? '-')
                            ->filter()
                            ->join("\n");
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (Loan $record): string => LoanResource::getUrl('view', ['record' => $record->user]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle)
            ->emptyStateHeading('Tidak ada pinjaman terlambat')
            ->emptyStateDescription('Semua pinjaman masih dalam batas waktu.')
            ->paginated([5, 10]);
    }
}
