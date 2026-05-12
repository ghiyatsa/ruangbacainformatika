<?php

namespace App\Filament\Resources\Theses;

use App\Filament\Resources\Theses\Pages\CreateThesis;
use App\Filament\Resources\Theses\Pages\EditThesis;
use App\Filament\Resources\Theses\Pages\ListTheses;
use App\Filament\Resources\Theses\Pages\ViewThesis;
use App\Filament\Resources\Theses\Schemas\ThesisForm;
use App\Filament\Resources\Theses\Schemas\ThesisInfolist;
use App\Filament\Resources\Theses\Tables\ThesesTable;
use App\Models\Thesis;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ThesisResource extends Resource
{
    protected static ?string $model = Thesis::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::AcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Tugas Akhir';

    protected static ?string $navigationLabel = 'Data Tesis';

    protected static ?string $modelLabel = 'Tesis';

    protected static ?string $recordTitleAttribute = 'student_id';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total data tesis';
    }

    public static function form(Schema $schema): Schema
    {
        return ThesisForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ThesisInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ThesesTable::configure($table);
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
            'index' => ListTheses::route('/'),
            'create' => CreateThesis::route('/create'),
            'view' => ViewThesis::route('/{record}'),
            'edit' => EditThesis::route('/{record}/edit'),
        ];
    }
}
