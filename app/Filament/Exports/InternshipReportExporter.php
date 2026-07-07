<?php

namespace App\Filament\Exports;

use App\Models\InternshipReport;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class InternshipReportExporter extends Exporter
{
    protected static ?string $model = InternshipReport::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('title')
                ->label('Judul')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('author_name')
                ->label('Nama')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('student_id')
                ->label('NIM')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('year')
                ->label('Tahun'),
            ExportColumn::make('abstract')
                ->label('Abstrak')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('keywords')
                ->label('Kata Kunci')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('view_count')
                ->label('Dilihat'),
            ExportColumn::make('created_at')
                ->label('Dibuat'),
            ExportColumn::make('updated_at')
                ->label('Diperbarui'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor data laporan KP selesai dan '.Number::format($export->successful_rows).' '.str('baris')->plural($export->successful_rows).' berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('baris')->plural($failedRowsCount).' gagal diekspor.';
        }

        return $body;
    }

    protected static function sanitizeForSpreadsheet(?string $value): string
    {
        $normalizedValue = trim((string) $value);

        if ($normalizedValue === '') {
            return '-';
        }

        return Str::startsWith($normalizedValue, ['=', '+', '-', '@'])
            ? "'{$normalizedValue}"
            : $normalizedValue;
    }
}
