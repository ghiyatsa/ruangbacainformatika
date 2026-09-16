<?php

namespace App\Filament\Resources\Docs;

use App\Filament\Resources\Docs\Pages\CreateDocumentation;
use App\Filament\Resources\Docs\Pages\EditDocumentation;
use App\Filament\Resources\Docs\Pages\ListDocumentations;
use App\Filament\Resources\Docs\Schemas\DocumentationForm;
use App\Filament\Resources\Docs\Tables\DocumentationsTable;
use App\Models\Documentation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DocumentationResource extends Resource
{
    protected static ?string $model = Documentation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::BookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Panduan Operasional';

    protected static ?int $navigationSort = 80;

    protected static ?string $modelLabel = 'Panduan';

    protected static ?string $pluralModelLabel = 'Panduan Operasional';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $draftCount = static::getModel()::query()->where('is_published', false)->count();

        return $draftCount > 0 ? (string) $draftCount : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Panduan masih draf';
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentationsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category:id,name', 'updatedBy:id,name']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentations::route('/'),
            'create' => CreateDocumentation::route('/create'),
            'edit' => EditDocumentation::route('/{record}/edit'),
        ];
    }
}
