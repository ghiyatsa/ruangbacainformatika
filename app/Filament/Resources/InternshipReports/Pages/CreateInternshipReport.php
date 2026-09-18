<?php

declare(strict_types=1);

namespace App\Filament\Resources\InternshipReports\Pages;

use App\Filament\Resources\InternshipReports\InternshipReportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInternshipReport extends CreateRecord
{
    protected static string $resource = InternshipReportResource::class;
}
