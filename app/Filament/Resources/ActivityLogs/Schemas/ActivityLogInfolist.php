<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use App\Models\ActivityLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make('Ringkasan Aktivitas')
                        ->columnSpan(1)
                        ->schema([
                            TextEntry::make('description')
                                ->label('Aktivitas'),
                            TextEntry::make('action')
                                ->label('Kode')
                                ->badge()
                                ->color('gray'),
                            TextEntry::make('subject_label')
                                ->label('Subjek')
                                ->placeholder('-'),
                            TextEntry::make('created_at')
                                ->label('Waktu')
                                ->dateTime('d M Y H:i'),
                        ]),
                    Section::make('Pelaku dan Detail')
                        ->columnSpan(2)
                        ->schema([
                            TextEntry::make('user.name')
                                ->label('Pelaku')
                                ->placeholder('Sistem'),
                            TextEntry::make('user.email')
                                ->label('Email')
                                ->placeholder('-'),
                            TextEntry::make('ip_address')
                                ->label('IP')
                                ->placeholder('-'),
                            TextEntry::make('user_agent')
                                ->label('Perangkat')
                                ->placeholder('-')
                                ->wrap(),
                            TextEntry::make('properties')
                                ->label('Detail')
                                ->state(fn (ActivityLog $record): string => json_encode($record->properties ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}')
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }
}
