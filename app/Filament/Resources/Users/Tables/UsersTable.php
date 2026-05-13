<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama, email, atau WhatsApp')
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
                TextColumn::make('roles.name')
                    ->badge()
                    ->label('Peran')
                    ->separator(', '),
                IconColumn::make('is_approved')
                    ->label('Disetujui')
                    ->boolean(),
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
                                fn (Builder $query): Builder => $query->where('created_at', '>=', Carbon::parse($data['registered_from'])->startOfDay()),
                            )
                            ->when(
                                filled($data['registered_until'] ?? null),
                                fn (Builder $query): Builder => $query->where('created_at', '<=', Carbon::parse($data['registered_until'])->endOfDay()),
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
