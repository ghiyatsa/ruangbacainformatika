<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostInfolist
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
                                Section::make('Konten Artikel')
                                    ->schema([
                                        TextEntry::make('title')
                                            ->label('Judul Artikel')
                                            ->weight('bold')
                                            ->size('lg'),
                                        TextEntry::make('slug')
                                            ->label('Slug')
                                            ->copyable(),
                                        TextEntry::make('content')
                                            ->label('Isi Artikel')
                                            ->html()
                                            ->placeholder('-')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(['lg' => 2]),

                        Group::make()
                            ->schema([
                                Section::make('Gambar Sampul')
                                    ->schema([
                                        ImageEntry::make('cover_image')
                                            ->hiddenLabel()
                                            ->alignCenter()
                                            ->defaultImageUrl(asset('images/article-placeholder.svg'))
                                            ->disk('public')
                                            ->imageWidth('100%')
                                            ->imageHeight('auto'),
                                    ]),

                                Section::make('Status & Publikasi')
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->label('Penulis')
                                            ->placeholder('-'),
                                        TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                'draft' => 'Draf',
                                                'pending' => 'Menunggu Peninjauan',
                                                'approved' => 'Diterbitkan',
                                                'rejected' => 'Perlu Perbaikan',
                                                default => $state,
                                            })
                                            ->color(fn (string $state): string => match ($state) {
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('reviewedBy.name')
                                            ->label('Peninjau')
                                            ->placeholder('-'),
                                        TextEntry::make('published_at')
                                            ->label('Tanggal Terbit')
                                            ->dateTime('d/m/Y H:i')
                                            ->placeholder('-'),
                                        TextEntry::make('view_count')
                                            ->label('Jumlah Dilihat')
                                            ->numeric()
                                            ->badge()
                                            ->color('gray'),
                                        TextEntry::make('rejection_reason')
                                            ->label('Catatan Penolakan / Perbaikan')
                                            ->placeholder('-')
                                            ->visible(fn ($record): bool => $record?->status === 'rejected')
                                            ->color('danger')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
