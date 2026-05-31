<?php

namespace App\Filament\Resources\CatalogReports\Schemas;

use App\Models\CatalogReport;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CatalogReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tindak Lanjut')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(CatalogReport::statusOptions())
                            ->required()
                            ->native(false),
                        Textarea::make('admin_notes')
                            ->label('Catatan admin')
                            ->rows(6)
                            ->placeholder('Tambahkan ringkasan hasil pengecekan atau tindak lanjut.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }
}
