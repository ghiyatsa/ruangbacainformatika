<?php

namespace App\Filament\Resources\InternshipReports\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InternshipReportForm
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
