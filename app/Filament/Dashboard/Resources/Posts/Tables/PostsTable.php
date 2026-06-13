<?php

namespace App\Filament\Dashboard\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul artikel...')
            ->emptyStateHeading('Belum ada artikel')
            ->emptyStateDescription('Daftar artikel blog Anda akan muncul di sini.')
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->disk('public')
                    ->visibility('public')
                    ->square()
                    ->size(40),

                TextColumn::make('title')
                    ->label('Judul Artikel')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Penulis')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('is_published')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Post $record): string => $record->is_published ? 'Terbit' : 'Draf')
                    ->color(fn (string $state): string => $state === 'Terbit' ? 'success' : 'gray'),

                TextColumn::make('published_at')
                    ->label('Tanggal Terbit')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('view_count')
                    ->label('Dilihat')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->placeholder('Semua Status')
                    ->trueLabel('Terbit')
                    ->falseLabel('Draf'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),
                    EditAction::make()
                        ->label('Ubah'),
                    DeleteAction::make()
                        ->label('Hapus'),
                ])
                    ->label('Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }
}
