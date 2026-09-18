<?php

use App\Filament\Exports\InternshipReportExporter;
use App\Filament\Exports\SkripsiExporter;
use App\Filament\Exports\ThesisExporter;
use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\Thesis;
use Filament\Actions\Exports\Models\Export;

it('defines the same academic columns for skripsi, thesis, and internship report', function () {
    $expected = [
        'id', 'title', 'author_name', 'student_id', 'year',
        'abstract', 'keywords', 'view_count', 'created_at', 'updated_at',
    ];

    foreach ([SkripsiExporter::class, ThesisExporter::class, InternshipReportExporter::class] as $exporterClass) {
        $columnNames = collect($exporterClass::getColumns())
            ->map(fn ($column): string => $column->getName())
            ->all();

        expect($columnNames)->toBe($expected);
    }
});

it('binds each academic exporter to its own model', function () {
    expect(SkripsiExporter::getModel())->toBe(Skripsi::class)
        ->and(ThesisExporter::getModel())->toBe(Thesis::class)
        ->and(InternshipReportExporter::getModel())->toBe(InternshipReport::class);
});

it('keeps the export notification wording per document type', function () {
    $export = new Export;
    $export->successful_rows = 12;
    $export->total_rows = 12;

    expect(SkripsiExporter::getCompletedNotificationBody($export))
        ->toBe('Ekspor data skripsi selesai dan 12 baris berhasil diekspor.')
        ->and(ThesisExporter::getCompletedNotificationBody($export))
        ->toBe('Ekspor data tesis selesai dan 12 baris berhasil diekspor.')
        ->and(InternshipReportExporter::getCompletedNotificationBody($export))
        ->toBe('Ekspor data laporan KP selesai dan 12 baris berhasil diekspor.');
});
