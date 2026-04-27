<?php

namespace App\Filament\Resources\Skripsis\Pages;

use App\Filament\Resources\Skripsis\SkripsiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSkripsis extends ListRecords
{
    protected static string $resource = SkripsiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
