<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    /**
     * @return array<int, Field>
     */
    public static function optionFormSchema(): array
    {
        return [
            static::nameField(),
            static::descriptionField(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Informasi dasar kategori buku.')
                    ->schema([
                        static::nameField()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

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
                    ->description('Penjelasan singkat tentang kategori ini.')
                    ->schema([
                        static::descriptionField(),
                    ]),
            ]);
    }

    protected static function nameField(): TextInput
    {
        return TextInput::make('name')
            ->label('Nama Kategori')
            ->required()
            ->maxLength(255)
            ->placeholder('Contoh: Fiksi, Nonfiksi, Sains');
    }

    protected static function descriptionField(): Textarea
    {
        return Textarea::make('description')
            ->label('Deskripsi')
            ->rows(8)
            ->maxLength(65535)
            ->placeholder('Jelaskan isi atau fokus kategori ini secara singkat...')
            ->columnSpanFull();
    }
}
