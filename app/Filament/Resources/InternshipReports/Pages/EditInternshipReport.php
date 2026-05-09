<?php

namespace App\Filament\Resources\InternshipReports\Pages;

use App\Filament\Resources\InternshipReports\InternshipReportResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInternshipReport extends EditRecord
{
    protected static string $resource = InternshipReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
