<?php

namespace App\Filament\Resources\CatalogReports\Tables;

use App\Models\CatalogReport;
use App\Support\AppTimezone;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CatalogReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('catalog_type')
                    ->label('Katalog')
                    ->formatStateUsing(fn (CatalogReport $record): string => $record->catalogTypeLabel())
                    ->badge()
                    ->color('gray'),
                TextColumn::make('catalog_title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('catalog_url')
                    ->label('URL')
                    ->url(fn (CatalogReport $record): ?string => $record->publicUrl(), shouldOpenInNewTab: true)
                    ->searchable()
                    ->toggleable()
                    ->limit(45),
                TextColumn::make('reporter_display_name')
                    ->label('Pelapor')
                    ->searchable(['reporter_name', 'reporter_email'])
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (CatalogReport $record): string => $record->statusLabel())
                    ->badge()
                    ->color(fn (CatalogReport $record): string => $record->statusColor())
                    ->sortable(),
                TextColumn::make('message')
                    ->label('Laporan')
                    ->limit(60)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('catalog_type')
                    ->label('Jenis Katalog')
                    ->options(CatalogReport::catalogTypeOptions()),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(CatalogReport::statusOptions()),
                Filter::make('created_at')
                    ->label('Tanggal Masuk')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari'),
                        DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query->when(
                            $data['from'],
                            function (Builder $query, $date): Builder {
                                [$startOfDay] = AppTimezone::dayRange($date);

                                return $query->where('created_at', '>=', $startOfDay);
                            }
                        )->when(
                            $data['until'],
                            function (Builder $query, $date): Builder {
                                [, $endOfDay] = AppTimezone::dayRange($date);

                                return $query->where('created_at', '<=', $endOfDay);
                            }
                        )
                    ),
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat'),
                EditAction::make()->label('Tindak Lanjut'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada laporan katalog')
            ->emptyStateDescription('Laporan katalog akan muncul di sini.');
    }
}
