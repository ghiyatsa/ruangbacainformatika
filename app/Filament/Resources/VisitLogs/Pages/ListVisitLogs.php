<?php

namespace App\Filament\Resources\VisitLogs\Pages;

use App\Filament\Resources\VisitLogs\VisitLogResource;
use Filament\Resources\Pages\ListRecords;

class ListVisitLogs extends ListRecords
{
    protected static string $resource = VisitLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
