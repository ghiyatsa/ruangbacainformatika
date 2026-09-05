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
                Section::make('Identitas Penerbit')
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

                Section::make('Profil Penerbit')
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
            ->placeholder('Contoh: Informatika Bandung');
    }

    protected static function cityField(): TextInput
    {
        return TextInput::make('city')
            ->label('Kota Penerbit')
            ->maxLength(255)
            ->placeholder('Contoh: Bandung');
    }

    protected static function descriptionField(): Textarea
    {
        return Textarea::make('description')
            ->label('Deskripsi')
            ->rows(8)
            ->maxLength(65535)
            ->placeholder('Profil singkat penerbit...')
            ->columnSpanFull();
    }
}
