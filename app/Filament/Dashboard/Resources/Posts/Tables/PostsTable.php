<?php

namespace App\Filament\Dashboard\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul')
            ->emptyStateHeading('Belum ada artikel')
            ->emptyStateDescription('Artikel Anda akan tampil di sini.')
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

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::statusLabel($state))
                    ->color(fn (string $state): string => static::statusColor($state))
                    ->sortable(),

                TextColumn::make('categories.name')
                    ->label('Kategori')
                    ->badge()
                    ->separator(', ')
                    ->limitList(2),

                TextColumn::make('published_at')
                    ->label('Terbit')
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Post::STATUS_DRAFT => 'Draf',
                        Post::STATUS_PENDING => 'Dalam Peninjauan',
                        Post::STATUS_APPROVED => 'Diterbitkan',
                        Post::STATUS_REJECTED => 'Perlu Perbaikan',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
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

    protected static function statusLabel(string $state): string
    {
        return match ($state) {
            Post::STATUS_DRAFT => 'Draf',
            Post::STATUS_PENDING => 'Dalam Peninjauan',
            Post::STATUS_APPROVED => 'Diterbitkan',
            Post::STATUS_REJECTED => 'Perlu Perbaikan',
            default => $state,
        };
    }

    protected static function statusColor(string $state): string
    {
        return match ($state) {
            Post::STATUS_PENDING => 'warning',
            Post::STATUS_APPROVED => 'success',
            Post::STATUS_REJECTED => 'danger',
            default => 'gray',
        };
    }
}
