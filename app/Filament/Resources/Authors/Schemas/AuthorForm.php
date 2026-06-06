<?php

namespace App\Filament\Resources\Authors\Schemas;

use App\Models\Author;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AuthorForm
{
    /**
     * @return array<int, Field>
     */
    public static function optionFormSchema(): array
    {
        return [
            static::nameField(),
            static::emailField(),
            static::bioField(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Data utama penulis.')
                    ->schema([
                        static::nameField()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Author::generateSlugPreview($state))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('authors', 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        static::emailField(),
                    ])
                    ->columns(2),

                Section::make('Biografi')
                    ->description('Keterangan tambahan penulis.')
                    ->schema([
                        static::bioField(),
                    ]),
            ]);
    }

    protected static function nameField(): TextInput
    {
        return TextInput::make('name')
            ->label('Nama Penulis')
            ->required()
            ->maxLength(255)
            ->placeholder('Nama lengkap');
    }

    protected static function emailField(): TextInput
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->unique('authors', 'email', ignoreRecord: true)
            ->maxLength(255)
            ->placeholder('penulis@example.com');
    }

    protected static function bioField(): Textarea
    {
        return Textarea::make('bio')
            ->label('Biografi')
            ->rows(8)
            ->maxLength(65535)
            ->placeholder('Profil singkat penulis...')
            ->columnSpanFull();
    }
}
