<?php

namespace App\Filament\Resources\InternshipReports\Pages;

use App\Filament\Resources\InternshipReports\InternshipReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInternshipReports extends ListRecords
{
    protected static string $resource = InternshipReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
