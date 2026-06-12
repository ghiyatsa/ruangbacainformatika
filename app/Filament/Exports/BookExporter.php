<?php

namespace App\Filament\Exports;

use App\Models\Book;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class BookExporter extends Exporter
{
    protected static ?string $model = Book::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('title')
                ->label('Judul')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('subtitle')
                ->label('Subjudul')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('slug')
                ->label('Slug')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('isbn')
                ->label('ISBN')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('issn')
                ->label('ISSN')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('ddc_code')
                ->label('DDC')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('description')
                ->label('Deskripsi')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('edition')
                ->label('Edisi')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('published_year')
                ->label('Tahun Terbit'),
            ExportColumn::make('pages')
                ->label('Jumlah Halaman')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('language')
                ->label('Bahasa')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('publisher.name')
                ->label('Penerbit')
                ->formatStateUsing(fn (?string $state): string => static::sanitizeForSpreadsheet($state)),
            ExportColumn::make('authors')
                ->label('Penulis')
                ->state(fn (Book $record): string => $record->authors->pluck('name')->implode(' | ')),
            ExportColumn::make('categories')
                ->label('Kategori')
                ->state(fn (Book $record): string => $record->categories->pluck('name')->implode(' | ')),
            ExportColumn::make('is_featured')
                ->label('Unggulan')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak'),
            ExportColumn::make('is_borrowable')
                ->label('Boleh Dipinjam')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak'),
            ExportColumn::make('is_published')
                ->label('Dipublikasikan')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak'),
            ExportColumn::make('view_count')
                ->label('Jumlah Dilihat'),
            ExportColumn::make('items_count')
                ->label('Stok')
                ->state(fn (Book $record): int => $record->items()->count()),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor data buku selesai dan '.Number::format($export->successful_rows).' '.str('baris')->plural($export->successful_rows).' berhasil diekspor.';

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
