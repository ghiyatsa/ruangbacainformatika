<?php

namespace App\Filament\Resources\InternshipReports\Schemas;

use App\Models\InternshipReport;
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
                Section::make('Sinkronisasi Similarity')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('similarity_sync_status')
                                    ->label('Status')
                                    ->state(fn (InternshipReport $record): string => $record->similaritySyncStatusLabel())
                                    ->badge()
                                    ->color(fn (InternshipReport $record): string => $record->similaritySyncStatusColor()),
                                TextEntry::make('similaritySyncStatus.last_operation')
                                    ->label('Operasi Terakhir')
                                    ->formatStateUsing(fn (InternshipReport $record): string => $record->similaritySyncStatus?->operationLabel() ?? 'Belum ada'),
                                TextEntry::make('similaritySyncStatus.attempts')
                                    ->label('Jumlah Percobaan')
                                    ->placeholder('0'),
                                TextEntry::make('similaritySyncStatus.last_attempt_at')
                                    ->label('Percobaan Terakhir')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('similaritySyncStatus.last_synced_at')
                                    ->label('Terakhir Sinkron')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('similaritySyncStatus.last_error')
                                    ->label('Error Terakhir')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
