<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Data kategori buku')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->placeholder('Contoh: Fiksi, Non-Fiksi, Sains, dll'),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('categories', 'slug', ignoreRecord: true)
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Deskripsi')
                    ->description('Penjelasan tentang kategori ini')
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(8)
                            ->maxLength(65535)
                            ->placeholder('Jelaskan apa isi kategori ini dan jenis buku apa yang termasuk di dalamnya...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
