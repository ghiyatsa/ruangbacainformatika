<?php

namespace App\Filament\Resources\PostCategories\Schemas;

use App\Models\PostCategory;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PostCategoryForm
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
                    ->description('Nama dan identitas kategori.')
                    ->schema([
                        static::nameField()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', PostCategory::generateSlugPreview($state))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('post_categories', 'slug', ignoreRecord: true)
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Deskripsi')
                    ->description('Keterangan tambahan bila diperlukan.')
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
            ->placeholder('Mis. Teknologi');
    }

    protected static function descriptionField(): Textarea
    {
        return Textarea::make('description')
            ->label('Deskripsi')
            ->rows(8)
            ->maxLength(65535)
            ->placeholder('Keterangan singkat kategori.')
            ->columnSpanFull();
    }
}
