<?php

namespace App\Filament\Resources\Publishers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PublisherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Data penerbit')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Penerbit')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->placeholder('Nama lengkap penerbit'),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('publishers', 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('city')
                            ->label('Kota')
                            ->maxLength(255)
                            ->placeholder('Jakarta, Surabaya, Bandung, dll'),
                    ])
                    ->columns(2),

                Section::make('Deskripsi')
                    ->description('Informasi tentang penerbit')
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(8)
                            ->maxLength(65535)
                            ->placeholder('Ceritakan tentang sejarah, visi, dan misi penerbit...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
