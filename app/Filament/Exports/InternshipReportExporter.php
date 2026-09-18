<?php

namespace App\Filament\Exports;

use App\Models\InternshipReport;

class InternshipReportExporter extends AcademicDocumentExporter
{
    protected static ?string $model = InternshipReport::class;

    protected static function documentLabel(): string
    {
        return 'laporan KP';
    }
}
