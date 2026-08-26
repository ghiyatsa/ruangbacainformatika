<?php

namespace App\Filament\Resources\MemberRegistrationClaims;

use App\Filament\Resources\MemberRegistrationClaims\Pages\ListMemberRegistrationClaims;
use App\Filament\Resources\MemberRegistrationClaims\Pages\ViewMemberRegistrationClaims;
use App\Filament\Resources\MemberRegistrationClaims\Schemas\MemberRegistrationClaimsInfolist;
use App\Filament\Resources\MemberRegistrationClaims\Tables\MemberRegistrationClaimsTable;
use App\Models\MemberRegistrationClaim;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MemberRegistrationClaimsResource extends Resource
{
    protected static ?string $model = MemberRegistrationClaim::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan Anggota';

    protected static ?string $navigationLabel = 'Klaim Registrasi';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Klaim Registrasi';

    protected static ?string $pluralModelLabel = 'Klaim Registrasi';

    protected static ?string $recordTitleAttribute = 'name';

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

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->where('status', MemberRegistrationClaim::STATUS_PENDING)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Klaim menunggu penautan akun';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MemberRegistrationClaimsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemberRegistrationClaimsTable::configure($table);
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
            'index' => ListMemberRegistrationClaims::route('/'),
            'view' => ViewMemberRegistrationClaims::route('/{record}'),
        ];
    }
}
