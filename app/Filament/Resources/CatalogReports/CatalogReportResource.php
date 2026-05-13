<?php

namespace App\Filament\Resources\CatalogReports;

use App\Filament\Resources\CatalogReports\Pages\EditCatalogReport;
use App\Filament\Resources\CatalogReports\Pages\ListCatalogReports;
use App\Filament\Resources\CatalogReports\Pages\ViewCatalogReport;
use App\Filament\Resources\CatalogReports\Schemas\CatalogReportForm;
use App\Filament\Resources\CatalogReports\Schemas\CatalogReportInfolist;
use App\Filament\Resources\CatalogReports\Tables\CatalogReportsTable;
use App\Models\CatalogReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CatalogReportResource extends Resource
{
    protected static ?string $model = CatalogReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Flag;

    protected static string|UnitEnum|null $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Laporan Katalog';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Laporan katalog';

    protected static ?string $pluralModelLabel = 'Laporan katalog';

    protected static ?string $recordTitleAttribute = 'catalog_title';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->where('status', CatalogReport::STATUS_PENDING)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Laporan menunggu tindak lanjut';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return CatalogReportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CatalogReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CatalogReportsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['reportable', 'user'])
            ->latest();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCatalogReports::route('/'),
            'view' => ViewCatalogReport::route('/{record}'),
            'edit' => EditCatalogReport::route('/{record}/edit'),
        ];
    }
}
