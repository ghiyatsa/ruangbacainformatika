<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Models\ContactMessage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make('Pesan Masuk')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama pengirim'),
                                TextEntry::make('email')
                                    ->label('Email'),
                                TextEntry::make('phone')
                                    ->label('Nomor telepon')
                                    ->placeholder('-'),
                                TextEntry::make('subject')
                                    ->label('Subjek'),
                                TextEntry::make('created_at')
                                    ->label('Dikirim pada')
                                    ->dateTime('d M Y H:i'),
                                TextEntry::make('message')
                                    ->label('Isi pesan')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(1),
                        Section::make('Penanganan Admin')
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn (ContactMessage $record): string => $record->statusLabel())
                                    ->badge()
                                    ->color(fn (ContactMessage $record): string => $record->statusColor()),
                                TextEntry::make('reviewed_at')
                                    ->label('Ditinjau pada')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('admin_notes')
                                    ->label('Catatan admin')
                                    ->placeholder('Belum ada catatan admin.')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
