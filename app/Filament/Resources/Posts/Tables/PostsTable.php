<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul artikel...')
            ->emptyStateHeading('Belum ada artikel')
            ->emptyStateDescription('Daftar artikel blog akan muncul di sini.')
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

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Post $record): string => match ($record->status) {
                        Post::STATUS_DRAFT => 'Draf',
                        Post::STATUS_PENDING => 'Butuh Review',
                        Post::STATUS_APPROVED => 'Disetujui',
                        Post::STATUS_REJECTED => 'Ditolak',
                        default => $record->status,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Draf' => 'gray',
                        'Butuh Review' => 'warning',
                        'Disetujui' => 'success',
                        'Ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Post::STATUS_DRAFT => 'Draf',
                        Post::STATUS_PENDING => 'Butuh Review',
                        Post::STATUS_APPROVED => 'Disetujui',
                        Post::STATUS_REJECTED => 'Ditolak',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),
                    EditAction::make()
                        ->label('Ubah / Review'),
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
