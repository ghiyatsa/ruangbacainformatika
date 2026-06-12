<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Services\BookCoverImageService;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
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
                        // Kolom Sampul Buku
                        Section::make('Sampul Buku')
                            ->schema([
                                ImageEntry::make('cover_image')
                                    ->hiddenLabel()
                                    ->alignCenter()
                                    ->defaultImageUrl(app(BookCoverImageService::class)->getDefaultCoverUrl())
                                    ->disk('public')
                                    ->extraImgAttributes([
                                        'class' => 'object-contain rounded-lg shadow-md max-h-80 bg-white p-2',
                                    ]),
                            ])
                            ->columnSpan(1),

                        // Kolom Detail Informasi
                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi Dasar')
                                    ->schema([
                                        TextEntry::make('title')
                                            ->label('Judul Buku')
                                            ->weight('bold')
                                            ->size('lg'),
                                        TextEntry::make('subtitle')
                                            ->label('Subjudul')
                                            ->placeholder('-'),
                                        TextEntry::make('slug')
                                            ->label('Slug')
                                            ->copyable(),
                                        TextEntry::make('ddc_code')
                                            ->label('Kode DDC')
                                            ->placeholder('-'),
                                        TextEntry::make('description')
                                            ->label('Deskripsi Singkat')
                                            ->placeholder('-')
                                            ->columnSpanFull(),
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
                                            ->label('Edisi')
                                            ->placeholder('-'),
                                        TextEntry::make('pages')
                                            ->label('Jumlah Halaman')
                                            ->placeholder('-'),
                                        TextEntry::make('language')
                                            ->label('Bahasa')
                                            ->placeholder('-'),
                                        TextEntry::make('isbn')
                                            ->label('ISBN')
                                            ->copyable()
                                            ->placeholder('-'),
                                        TextEntry::make('issn')
                                            ->label('ISSN')
                                            ->copyable()
                                            ->placeholder('-'),
                                    ])
                                    ->columns(2),

                                Section::make('Relasi & Meta')
                                    ->schema([
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
                                        TextEntry::make('view_count')
                                            ->label('Jumlah Dilihat')
                                            ->numeric()
                                            ->badge()
                                            ->color('gray'),
                                    ])
                                    ->columns(2),

                                Section::make('Status & Visibilitas')
                                    ->schema([
                                        IconEntry::make('is_published')
                                            ->label('Dipublikasikan')
                                            ->boolean(),
                                        IconEntry::make('is_featured')
                                            ->label('Unggulan')
                                            ->boolean(),
                                        IconEntry::make('is_borrowable')
                                            ->label('Boleh Dipinjam')
                                            ->boolean(),
                                    ])
                                    ->columns(3),
                            ])
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
