<?php

namespace App\Filament\Resources\KioskDevices\Pages;

use App\Filament\Resources\KioskDevices\KioskDeviceResource;
use Filament\Resources\Pages\ListRecords;

class ListKioskDevices extends ListRecords
{
    protected static string $resource = KioskDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
