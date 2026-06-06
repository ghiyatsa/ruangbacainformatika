<?php

namespace App\Filament\Resources\InternshipReports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InternshipReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Laporan PKL / Magang')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Judul')
                                    ->columnSpanFull(),
                                TextEntry::make('author_name')
                                    ->label('Nama Mahasiswa'),
                                TextEntry::make('student_id')
                                    ->label('NIM'),
                                TextEntry::make('year')
                                    ->label('Tahun'),
                                TextEntry::make('keywords')
                                    ->label('Kata Kunci')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                                TextEntry::make('abstract')
                                    ->label('Abstrak')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->label('Diperbarui')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                            ]),
                    ]),
            ]);
    }
}
