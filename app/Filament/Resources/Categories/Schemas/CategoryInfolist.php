<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 3,
                ])
                    ->columnSpanFull()
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Detail Kategori')
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Nama Kategori')
                                            ->weight('bold'),
                                        TextEntry::make('slug')
                                            ->label('Slug')
                                            ->copyable(),
                                        TextEntry::make('description')
                                            ->label('Deskripsi')
                                            ->placeholder('Belum melampirkan deskripsi')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(['lg' => 2]),

                        Group::make()
                            ->schema([
                                Section::make('Metadata Sistem')
                                    ->schema([
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
                            ->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }
}
