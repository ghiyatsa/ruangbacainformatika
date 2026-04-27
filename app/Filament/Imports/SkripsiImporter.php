<?php

namespace App\Filament\Imports;

use App\Models\Skripsi;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class SkripsiImporter extends Importer
{
    protected static ?string $model = Skripsi::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('title')
                ->label('Judul')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('author_name')
                ->label('Nama')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('student_id')
                ->label('NIM')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('year')
                ->label('Tahun')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('abstract')
                ->label('Abstrak'),
            ImportColumn::make('keywords')
                ->label('Kata Kunci')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): Skripsi
    {
        return Skripsi::firstOrNew([
            'student_id' => $this->data['student_id'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor skripsi selesai dan '.Number::format($import->successful_rows).' '.str('baris')->plural($import->successful_rows).' berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('baris')->plural($failedRowsCount).' gagal diimpor.';
        }

        return $body;
    }
}
