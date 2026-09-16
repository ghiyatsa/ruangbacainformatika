<?php

namespace App\Filament\Resources\Docs\Schemas;

use App\Models\DocumentationCategory;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Panduan')
                    ->schema([
                        Select::make('documentation_category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->options(
                                fn (): array => DocumentationCategory::query()
                                    ->orderBy('sort_order')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all(),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->helperText('Dibuat otomatis dari judul. Ubah hanya bila perlu.')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('summary')
                            ->label('Ringkasan')
                            ->helperText('Muncul sebagai ringkasan singkat di daftar panduan.')
                            ->rows(3)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->helperText('Angka kecil tampil lebih dahulu.')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Toggle::make('is_published')
                            ->label('Terbitkan')
                            ->helperText('Panduan yang tidak diterbitkan tetap tersimpan sebagai draf.')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Isi Panduan')
                    ->schema([
                        RichEditor::make('content')
                            ->label('Isi')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'h2',
                                'h3',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'codeBlock',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
