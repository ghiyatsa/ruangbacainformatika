<?php

namespace App\Filament\Resources\Loans\RelationManagers;

use App\Filament\Resources\Loans\Pages\ViewLoan;
use App\Support\AppTimezone;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'loanItems';

    protected static bool $isLazy = false;

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === ViewLoan::class
            && (Auth::user()?->hasAdministrativeRole() ?? false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('bookItem.book.title')
            ->columns([
                TextColumn::make('loan.borrowed_at')
                    ->label('Waktu Pinjam')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('loan.id')
                    ->label('ID Pinjam')
                    ->formatStateUsing(fn (int $state): string => "#{$state}")
                    ->sortable(),
                TextColumn::make('bookItem.book.title')
                    ->label('Judul Buku')
                    ->searchable(),
                TextColumn::make('bookItem.book.isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('bookItem.internal_code')
                    ->label('Kode Eksemplar')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('bookItem.status')
                    ->label('Status Item')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Tersedia',
                        'borrowed' => 'Dipinjam',
                        'maintenance' => 'Perawatan',
                        'reserved' => 'Reservasi',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'borrowed' => 'warning',
                        'maintenance' => 'danger',
                        'reserved' => 'info',
                        default => 'gray',
                    }),
                IconColumn::make('returned_at')
                    ->label('Sudah Kembali')
                    ->boolean()
                    ->getStateUsing(fn (Model $record): bool => $record->returned_at !== null),
                TextColumn::make('returned_at')
                    ->label('Waktu Kembali')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ])
            ->filters([
                TernaryFilter::make('returned')
                    ->label('Status Kembali')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Kembali')
                    ->falseLabel('Belum Kembali')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('loan_items.returned_at'),
                        false: fn (Builder $query) => $query->whereNull('loan_items.returned_at'),
                    ),
                Filter::make('borrowed_at')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query->when(
                            $data['from'],
                            function (Builder $query, $date): Builder {
                                [$startOfDay] = AppTimezone::dayRange($date);

                                return $query->whereHas('loan', fn ($q) => $q->where('borrowed_at', '>=', $startOfDay));
                            },
                        )->when(
                            $data['until'],
                            function (Builder $query, $date): Builder {
                                [, $endOfDay] = AppTimezone::dayRange($date);

                                return $query->whereHas('loan', fn ($q) => $q->where('borrowed_at', '<=', $endOfDay));
                            },
                        ),
                    ),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
