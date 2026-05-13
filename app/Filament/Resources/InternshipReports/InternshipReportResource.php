<?php

namespace App\Filament\Resources\InternshipReports;

use App\Filament\Resources\InternshipReports\Pages\CreateInternshipReport;
use App\Filament\Resources\InternshipReports\Pages\EditInternshipReport;
use App\Filament\Resources\InternshipReports\Pages\ListInternshipReports;
use App\Filament\Resources\InternshipReports\Pages\ViewInternshipReport;
use App\Filament\Resources\InternshipReports\Schemas\InternshipReportForm;
use App\Filament\Resources\InternshipReports\Schemas\InternshipReportInfolist;
use App\Filament\Resources\InternshipReports\Tables\InternshipReportsTable;
use App\Models\InternshipReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InternshipReportResource extends Resource
{
    protected static ?string $model = InternshipReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Tugas Akhir';

    protected static ?string $navigationLabel = 'Laporan KP';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Laporan KP';

    protected static ?string $pluralModelLabel = 'Laporan KP';

    protected static ?string $recordTitleAttribute = 'student_id';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total laporan KP';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'gray';
    }

    public static function form(Schema $schema): Schema
    {
        return InternshipReportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InternshipReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InternshipReportsTable::configure($table);
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
            'index' => ListInternshipReports::route('/'),
            'create' => CreateInternshipReport::route('/create'),
            'view' => ViewInternshipReport::route('/{record}'),
            'edit' => EditInternshipReport::route('/{record}/edit'),
        ];
    }
}
