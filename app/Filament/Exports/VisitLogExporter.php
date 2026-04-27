<?php

namespace App\Filament\Exports;

use App\Models\VisitLog;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class VisitLogExporter extends Exporter
{
    protected static ?string $model = VisitLog::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('kioskDevice.name')
                ->label('Perangkat Kiosk')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('name')
                ->label('Nama Pengunjung')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('visitor_type')
                ->label('Jenis Pengunjung')
                ->formatStateUsing(fn (string $state): string => VisitLog::visitorTypeOptions()[$state] ?? $state),
            ExportColumn::make('identity_number')
                ->label('Nomor Identitas')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('institution')
                ->label('Prodi / Instansi')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('phone')
                ->label('WhatsApp')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('purpose')
                ->label('Tujuan Kunjungan')
                ->formatStateUsing(fn (string $state): string => VisitLog::purposeOptions()[$state] ?? $state),
            ExportColumn::make('notes')
                ->label('Catatan')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('visited_at')
                ->label('Waktu Kunjungan')
                ->formatStateUsing(fn ($state): string => $state?->translatedFormat('d M Y H:i') ?? '-'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor data kunjungan selesai dan '.Number::format($export->successful_rows).' '.str('baris')->plural($export->successful_rows).' berhasil diekspor.';

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
