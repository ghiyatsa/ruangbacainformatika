<?php

namespace App\Filament\Resources\VisitLogs\Tables;

use App\Filament\Exports\VisitLogExporter;
use App\Models\VisitLog;
use Carbon\CarbonInterface;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama, identitas, atau instansi')
            ->emptyStateHeading('Belum ada data kunjungan')
            ->emptyStateDescription('Data kunjungan akan muncul di sini.')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->columns([
                TextColumn::make('visited_at')
                    ->label('Waktu Kunjungan')
                    ->formatStateUsing(
                        fn (?CarbonInterface $state): string => $state?->copy()->setTimezone(VisitLog::adminTimezone())->translatedFormat('d M Y H:i') ?? '-',
                    )
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('visitor_type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => VisitLog::visitorTypeOptions()[$state] ?? $state),
                TextColumn::make('identity_number')
                    ->label('Nomor Identitas')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('institution')
                    ->label('Prodi / Instansi')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('purpose')
                    ->label('Tujuan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => VisitLog::purposeOptions()[$state] ?? $state),
                TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('visitor_type')
                    ->label('Jenis Pengunjung')
                    ->options(VisitLog::visitorTypeOptions()),
                SelectFilter::make('purpose')
                    ->label('Tujuan Kunjungan')
                    ->options(VisitLog::purposeOptions()),
                Filter::make('visited_between')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('visited_from')
                            ->label('Dari'),
                        DatePicker::make('visited_until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->visitedBetween(
                            $data['visited_from'] ?? null,
                            $data['visited_until'] ?? null,
                        );
                    }),
                Filter::make('today')
                    ->label('Hari ini')
                    ->query(function (Builder $query): Builder {
                        [$startOfDay, $endOfDay] = VisitLog::adminDayRange();

                        return $query->whereBetween('visited_at', [$startOfDay, $endOfDay]);
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),
                    EditAction::make()
                        ->label('Ubah'),
                    DeleteAction::make()
                        ->label('Hapus'),
                ])
                    ->label('Aksi'),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Ekspor')
                    ->exporter(VisitLogExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->defaultSort('visited_at', 'desc');
    }
}
