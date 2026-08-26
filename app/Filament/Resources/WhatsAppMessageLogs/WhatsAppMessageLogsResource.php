<?php

namespace App\Filament\Resources\WhatsAppMessageLogs;

use App\Filament\Resources\WhatsAppMessageLogs\Pages\ListWhatsAppMessageLogs;
use App\Filament\Resources\WhatsAppMessageLogs\Pages\ViewWhatsAppMessageLogs;
use App\Filament\Resources\WhatsAppMessageLogs\Schemas\WhatsAppMessageLogsInfolist;
use App\Filament\Resources\WhatsAppMessageLogs\Tables\WhatsAppMessageLogsTable;
use App\Models\WhatsAppMessageLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class WhatsAppMessageLogsResource extends Resource
{
    protected static ?string $model = WhatsAppMessageLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Komunikasi';

    protected static ?string $navigationLabel = 'Log WhatsApp';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Log WhatsApp';

    protected static ?string $pluralModelLabel = 'Log WhatsApp';

    protected static ?string $recordTitleAttribute = 'notification_type';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WhatsAppMessageLogsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsAppMessageLogsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user')->latest();
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
            'index' => ListWhatsAppMessageLogs::route('/'),
            'view' => ViewWhatsAppMessageLogs::route('/{record}'),
        ];
    }
}
