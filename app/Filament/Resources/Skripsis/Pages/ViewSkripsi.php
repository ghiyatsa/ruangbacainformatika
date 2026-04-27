<?php

namespace App\Filament\Resources\Skripsis\Pages;

use App\Filament\Resources\Skripsis\SkripsiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSkripsi extends ViewRecord
{
    protected static string $resource = SkripsiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
