<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten Artikel')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Artikel')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                                'slug',
                                Str::slug($state ?? ''),
                            ))
                            ->columnSpan(1),

                        TextInput::make('slug')
                            ->label('Slug / URL')
                            ->required()
                            ->unique(table: 'posts', column: 'slug', ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),

                        Textarea::make('summary')
                            ->label('Ringkasan')
                            ->placeholder('Tulis ringkasan singkat artikel untuk preview dan SEO...')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Isi Artikel')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Persetujuan & Publikasi')
                    ->schema([
                        Select::make('user_id')
                            ->label('Penulis')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->default(fn () => auth()->id())
                            ->required(),

                        FileUpload::make('cover_image')
                            ->label('Gambar Sampul')
                            ->image()
                            ->disk('public')
                            ->directory('posts/covers')
                            ->visibility('public')
                            ->maxSize(2048),

                        Select::make('status')
                            ->label('Status Artikel')
                            ->options([
                                Post::STATUS_DRAFT => 'Draf',
                                Post::STATUS_PENDING => 'Menunggu Review',
                                Post::STATUS_APPROVED => 'Setujui & Terbitkan',
                                Post::STATUS_REJECTED => 'Tolak',
                            ])
                            ->required()
                            ->live(),

                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->placeholder('Tulis catatan mengapa artikel ditolak...')
                            ->required(fn (Get $get): bool => $get('status') === Post::STATUS_REJECTED)
                            ->visible(fn (Get $get): bool => $get('status') === Post::STATUS_REJECTED)
                            ->maxLength(500),
                    ]),
            ]);
    }
}
