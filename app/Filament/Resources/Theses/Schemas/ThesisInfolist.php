<?php

namespace App\Filament\Resources\Theses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ThesisInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Judul'),
                TextEntry::make('author_name')
                    ->label('Nama Mahasiswa'),
                TextEntry::make('student_id')
                    ->label('NIM'),
                TextEntry::make('year')
                    ->label('Tahun'),
                TextEntry::make('abstract')
                    ->label('Abstrak')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('keywords')
                    ->label('Kata Kunci')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
            ]);
    }
}
