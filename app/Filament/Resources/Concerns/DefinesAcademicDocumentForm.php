<?php

namespace App\Filament\Resources\Concerns;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Skema form bersama untuk dokumen akademik (skripsi, tesis, laporan KP).
 *
 * Ketiga model memiliki field yang identik; tiap resource cukup memakai trait
 * ini agar tidak terjadi duplikasi definisi form.
 */
trait DefinesAcademicDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                TextInput::make('author_name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('student_id')
                    ->label('NIM')
                    ->required(),
                TextInput::make('year')
                    ->label('Tahun')
                    ->numeric()
                    ->required(),
                Textarea::make('abstract')
                    ->label('Abstrak')
                    ->columnSpanFull(),
                TextInput::make('keywords')
                    ->label('Kata Kunci'),
            ]);
    }
}
