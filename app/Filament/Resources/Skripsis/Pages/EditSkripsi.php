<?php

namespace App\Filament\Resources\Skripsis\Pages;

use App\Filament\Resources\Skripsis\SkripsiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSkripsi extends EditRecord
{
    protected static string $resource = SkripsiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
