<?php

namespace App\Filament\Resources\VisitLogs;

use App\Filament\Resources\VisitLogs\Pages\EditVisitLog;
use App\Filament\Resources\VisitLogs\Pages\ListVisitLogs;
use App\Filament\Resources\VisitLogs\Schemas\VisitLogForm;
use App\Filament\Resources\VisitLogs\Tables\VisitLogsTable;
use App\Models\VisitLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VisitLogResource extends Resource
{
    protected static ?string $model = VisitLog::class;

    protected static string|UnitEnum|null $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Kunjungan';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Kunjungan';

    protected static ?string $pluralModelLabel = 'Kunjungan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()
            ->whereDate('visited_at', today())
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Kunjungan hari ini';
    }

    public static function form(Schema $schema): Schema
    {
        return VisitLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitLogsTable::configure($table);
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
            'index' => ListVisitLogs::route('/'),
            'edit' => EditVisitLog::route('/{record}/edit'),
        ];
    }
}
