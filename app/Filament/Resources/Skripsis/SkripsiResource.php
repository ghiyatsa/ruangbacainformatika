<?php

namespace App\Filament\Resources\Skripsis;

use App\Filament\Resources\Skripsis\Pages\CreateSkripsi;
use App\Filament\Resources\Skripsis\Pages\EditSkripsi;
use App\Filament\Resources\Skripsis\Pages\ListSkripsis;
use App\Filament\Resources\Skripsis\Pages\ViewSkripsi;
use App\Filament\Resources\Skripsis\Schemas\SkripsiForm;
use App\Filament\Resources\Skripsis\Schemas\SkripsiInfolist;
use App\Filament\Resources\Skripsis\Tables\SkripsisTable;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SkripsiResource extends Resource
{
    protected static ?string $model = Skripsi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Newspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Tugas Akhir';

    protected static ?string $navigationLabel = 'Skripsi';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Skripsi';

    protected static ?string $pluralModelLabel = 'Skripsi';

    protected static ?string $recordTitleAttribute = 'student_id';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $failedCount = SimilaritySyncStatus::query()
            ->where('status', SimilaritySyncStatus::STATUS_FAILED)
            ->count();

        if ($failedCount === 0) {
            return 'Total skripsi';
        }

        return "Total skripsi • {$failedCount} gagal sinkron";
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'gray';
    }

    public static function form(Schema $schema): Schema
    {
        return SkripsiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SkripsiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkripsisTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('similaritySyncStatus');
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
            'index' => ListSkripsis::route('/'),
            'create' => CreateSkripsi::route('/create'),
            'view' => ViewSkripsi::route('/{record}'),
            'edit' => EditSkripsi::route('/{record}/edit'),
        ];
    }
}
