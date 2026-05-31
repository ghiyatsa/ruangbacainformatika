<?php

namespace App\Filament\Resources\CatalogReports\Pages;

use App\Filament\Resources\CatalogReports\CatalogReportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCatalogReport extends ViewRecord
{
    protected static string $resource = CatalogReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Tindak Lanjut'),
        ];
    }
}
