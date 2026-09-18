<?php

namespace App\Filament\Exports;

use App\Models\Skripsi;

class SkripsiExporter extends AcademicDocumentExporter
{
    protected static ?string $model = Skripsi::class;

    protected static function documentLabel(): string
    {
        return 'skripsi';
    }
}
