<?php

namespace App\Filament\Resources\Authors\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Data penulis')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Penulis')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->placeholder('Nama lengkap penulis'),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('authors', 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique('authors', 'email', ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('penulis@example.com'),
                    ])
                    ->columns(2),

                Section::make('Biografi')
                    ->description('Informasi lengkap tentang penulis')
                    ->schema([
                        Textarea::make('bio')
                            ->label('Biografi')
                            ->rows(8)
                            ->maxLength(65535)
                            ->placeholder('Ceritakan tentang latar belakang dan pencapaian penulis...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
