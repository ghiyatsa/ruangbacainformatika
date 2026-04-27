<?php

namespace App\Filament\Resources\KioskDevices\Pages;

use App\Filament\Resources\KioskDevices\KioskDeviceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKioskDevice extends EditRecord
{
    protected static string $resource = KioskDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(fn (): bool => false),
        ];
    }
}
