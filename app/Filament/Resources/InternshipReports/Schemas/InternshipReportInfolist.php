<?php

namespace App\Filament\Resources\InternshipReports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InternshipReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('author_name'),
                TextEntry::make('student_id'),
                TextEntry::make('year'),
                TextEntry::make('abstract')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('keywords')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
