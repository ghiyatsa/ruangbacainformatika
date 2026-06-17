<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Services\BookCoverImageService;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 3,
                ])
                    ->columnSpanFull()
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Identitas Buku')
                                    ->schema([
                                        TextEntry::make('title')
                                            ->label('Judul Buku')
                                            ->weight('bold')
                                            ->size('lg'),
                                        TextEntry::make('subtitle')
                                            ->label('Subjudul')
                                            ->placeholder('Belum melampirkan subjudul'),
                                        TextEntry::make('slug')
                                            ->label('Slug')
                                            ->copyable(),
                                        TextEntry::make('ddc_code')
                                            ->label('Kode DDC')
                                            ->placeholder('Belum diklasifikasikan'),
                                        TextEntry::make('description')
                                            ->label('Deskripsi Singkat')
                                            ->placeholder('-')
                                            ->columnSpanFull(),
                                        TextEntry::make('isbn')
                                            ->label('ISBN')
                                            ->visible(fn ($record): bool => filled($record?->isbn))
                                            ->copyable()
                                            ->placeholder('-'),
                                        TextEntry::make('issn')
                                            ->label('ISSN')
                                            ->visible(fn ($record): bool => filled($record?->issn))
                                            ->copyable()
                                            ->placeholder('-'),
                                        TextEntry::make('language')
                                            ->label('Bahasa')
                                            ->placeholder('-'),
                                    ])
                                    ->columns(2),

                                Section::make('Detail Publikasi')
                                    ->schema([
                                        TextEntry::make('publisher.name')
                                            ->label('Penerbit')
                                            ->placeholder('-'),
                                        TextEntry::make('published_year')
                                            ->label('Tahun Terbit')
                                            ->placeholder('-'),
                                        TextEntry::make('edition')
                                            ->label('Edisi / Volume')
                                            ->visible(fn ($record): bool => filled($record?->issn))
                                            ->placeholder('-'),
                                        TextEntry::make('pages')
                                            ->label('Jumlah Halaman')
                                            ->visible(fn ($record): bool => filled($record?->issn))
                                            ->placeholder('-'),
                                        TextEntry::make('authors.name')
                                            ->label('Penulis')
                                            ->badge()
                                            ->color('warning')
                                            ->placeholder('Tidak ada penulis'),
                                        TextEntry::make('categories.name')
                                            ->label('Kategori')
                                            ->badge()
                                            ->color('success')
                                            ->placeholder('Tidak ada kategori'),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(['lg' => 2]),

                        Group::make()
                            ->schema([
                                Section::make('Cover Buku')
                                    ->schema([
                                        ImageEntry::make('cover_image')
                                            ->hiddenLabel()
                                            ->alignCenter()
                                            ->defaultImageUrl(app(BookCoverImageService::class)->getDefaultCoverUrl())
                                            ->disk('public')
                                            ->imageWidth('100%')
                                            ->imageHeight('auto'),
                                    ]),

                                Section::make('Status & Visibilitas')
                                    ->schema([
                                        IconEntry::make('is_published')
                                            ->label('Dipublikasikan')
                                            ->boolean(),
                                        IconEntry::make('is_featured')
                                            ->label('Buku Unggulan')
                                            ->boolean(),
                                        IconEntry::make('is_borrowable')
                                            ->label('Boleh Dipinjam')
                                            ->boolean(),
                                        TextEntry::make('view_count')
                                            ->label('Jumlah Dilihat')
                                            ->numeric()
                                            ->badge()
                                            ->color('gray'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
