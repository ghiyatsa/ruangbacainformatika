<?php

namespace App\Filament\Resources\Skripsis\Schemas;

use App\Models\Skripsi;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SkripsiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Skripsi')
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
                                    ->label('Nama mahasiswa'),
                                TextEntry::make('student_id')
                                    ->label('NIM'),
                                TextEntry::make('year')
                                    ->label('Tahun'),
                                TextEntry::make('keywords')
                                    ->label('Kata kunci')
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
                    ])
                    ->columnSpanFull(),
                Section::make('Sinkronisasi Similarity')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('similarity_sync_status')
                                    ->label('Status')
                                    ->state(fn (Skripsi $record): string => $record->similaritySyncStatusLabel())
                                    ->badge()
                                    ->color(fn (Skripsi $record): string => $record->similaritySyncStatusColor()),
                                TextEntry::make('similaritySyncStatus.last_operation')
                                    ->label('Operasi terakhir')
                                    ->formatStateUsing(fn (Skripsi $record): string => $record->similaritySyncStatus?->operationLabel() ?? 'Belum ada')
                                    ->placeholder('-'),
                                TextEntry::make('similaritySyncStatus.attempts')
                                    ->label('Jumlah percobaan')
                                    ->placeholder('0'),
                                TextEntry::make('similaritySyncStatus.last_attempt_at')
                                    ->label('Percobaan terakhir')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('similaritySyncStatus.last_synced_at')
                                    ->label('Terakhir berhasil')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('similaritySyncStatus.last_error')
                                    ->label('Error terakhir')
                                    ->placeholder('Belum ada error.')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
