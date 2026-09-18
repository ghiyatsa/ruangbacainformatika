<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Thesis;

class ThesisExporter extends AcademicDocumentExporter
{
    protected static ?string $model = Thesis::class;

    protected static function documentLabel(): string
    {
        return 'tesis';
    }
}
