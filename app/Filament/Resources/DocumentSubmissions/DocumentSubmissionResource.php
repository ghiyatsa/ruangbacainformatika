<?php

namespace App\Filament\Resources\DocumentSubmissions;

use App\Filament\Resources\DocumentSubmissions\Pages\ListDocumentSubmissions;
use App\Filament\Resources\DocumentSubmissions\Pages\ViewDocumentSubmission;
use App\Filament\Resources\DocumentSubmissions\Schemas\DocumentSubmissionInfolist;
use App\Filament\Resources\DocumentSubmissions\Tables\DocumentSubmissionsTable;
use App\Models\DocumentSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class DocumentSubmissionResource extends Resource
{
    protected static ?string $model = DocumentSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::DocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan Anggota';

    protected static ?string $navigationLabel = 'Distribusi Dokumen';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Pengajuan Distribusi';

    protected static ?string $pluralModelLabel = 'Distribusi Dokumen';

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record?->user?->identityNumber() ?? $record?->title;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->where('status', DocumentSubmission::STATUS_PENDING)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pengajuan dokumen menunggu verifikasi';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentSubmissionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentSubmissionsTable::configure($table);
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
            'index' => ListDocumentSubmissions::route('/'),
            'view' => ViewDocumentSubmission::route('/{record}'),
        ];
    }
}
