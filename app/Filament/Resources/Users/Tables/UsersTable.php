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
            ->emptyStateDescription('Pengguna baru akan muncul setelah registrasi atau dibuat dari panel admin.')
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
                    ->label('Roles')
                    ->separator(', '),
                IconColumn::make('is_approved')
                    ->label('Status')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Persetujuan Akun')
                    ->placeholder('Semua akun')
                    ->trueLabel('Sudah disetujui')
                    ->falseLabel('Belum disetujui'),
                Filter::make('registered_between')
                    ->label('Rentang Pendaftaran')
                    ->form([
                        DatePicker::make('registered_from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('registered_until')
                            ->label('Sampai Tanggal'),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
