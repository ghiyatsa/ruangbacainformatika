<?php

namespace App\Filament\Resources\InternshipReports\Pages;

use App\Filament\Resources\InternshipReports\InternshipReportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInternshipReport extends ViewRecord
{
    protected static string $resource = InternshipReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
