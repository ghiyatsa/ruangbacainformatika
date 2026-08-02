<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CatalogReports\CatalogReportResource;
use App\Models\CatalogReport;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;

class CatalogReportsTableWidget extends BaseTableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 5;

    protected static ?string $heading = 'Laporan Umpan Balik Katalog';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(CatalogReport::query()->latest())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Masuk')
                    ->since()
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
                    ->limit(70)
                    ->wrap(),
            ])
            ->recordActions([
                Action::make('lihat')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (CatalogReport $record): string => CatalogReportResource::getUrl('view', ['record' => $record])),
            ])
            ->recordUrl(fn (CatalogReport $record): string => CatalogReportResource::getUrl('view', ['record' => $record]))
            ->emptyStateIcon(Heroicon::OutlinedFlag)
            ->emptyStateHeading('Belum ada umpan balik')
            ->emptyStateDescription('Umpan balik katalog akan muncul di sini.')
            ->paginated([5, 10]);
    }
}
