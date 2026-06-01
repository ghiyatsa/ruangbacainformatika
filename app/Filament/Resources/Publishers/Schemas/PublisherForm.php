<?php

namespace App\Filament\Resources\Publishers\Schemas;

use App\Models\Publisher;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PublisherForm
{
    /**
     * @return array<int, Field>
     */
    public static function optionFormSchema(): array
    {
        return [
            static::nameField(),
            static::cityField(),
            static::descriptionField(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Informasi dasar penerbit.')
                    ->schema([
                        static::nameField()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Publisher::generateSlugPreview($state))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('publishers', 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        static::cityField(),
                    ])
                    ->columns(2),

                Section::make('Deskripsi')
                    ->description('Informasi tambahan tentang penerbit.')
                    ->schema([
                        static::descriptionField(),
                    ]),
            ]);
    }

    protected static function nameField(): TextInput
    {
        return TextInput::make('name')
            ->label('Nama Penerbit')
            ->required()
            ->maxLength(255)
            ->placeholder('Nama lengkap penerbit');
    }

    protected static function cityField(): TextInput
    {
        return TextInput::make('city')
            ->label('Kota')
            ->maxLength(255)
            ->placeholder('Jakarta, Surabaya, Bandung');
    }

    protected static function descriptionField(): Textarea
    {
        return Textarea::make('description')
            ->label('Deskripsi')
            ->rows(8)
            ->maxLength(65535)
            ->placeholder('Tuliskan profil singkat penerbit...')
            ->columnSpanFull();
    }
}
