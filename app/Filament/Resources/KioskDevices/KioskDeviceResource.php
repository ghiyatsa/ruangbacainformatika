<?php

namespace App\Filament\Resources\KioskDevices;

use App\Filament\Resources\KioskDevices\Pages\EditKioskDevice;
use App\Filament\Resources\KioskDevices\Pages\ListKioskDevices;
use App\Filament\Resources\KioskDevices\Schemas\KioskDeviceForm;
use App\Filament\Resources\KioskDevices\Tables\KioskDevicesTable;
use App\Models\KioskDevice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KioskDeviceResource extends Resource
{
    protected static ?string $model = KioskDevice::class;

    protected static string|UnitEnum|null $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Perangkat Kiosk';

    protected static ?string $modelLabel = 'Perangkat Kiosk';

    protected static ?string $pluralModelLabel = 'Perangkat Kiosk';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()
            ->where('status', KioskDevice::STATUS_PENDING)
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Perangkat yang menunggu persetujuan';
    }

    public static function form(Schema $schema): Schema
    {
        return KioskDeviceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KioskDevicesTable::configure($table);
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
            'index' => ListKioskDevices::route('/'),
            'edit' => EditKioskDevice::route('/{record}/edit'),
        ];
    }
}
