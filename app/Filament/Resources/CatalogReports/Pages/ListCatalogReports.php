<?php

namespace App\Filament\Resources\CatalogReports\Pages;

use App\Filament\Resources\CatalogReports\CatalogReportResource;
use Filament\Resources\Pages\ListRecords;

class ListCatalogReports extends ListRecords
{
    protected static string $resource = CatalogReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
