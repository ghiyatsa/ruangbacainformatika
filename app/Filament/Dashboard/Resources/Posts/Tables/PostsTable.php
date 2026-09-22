<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\Posts\Tables;

use App\Models\Post;
use App\Services\Post\PostRevisionService;
use Filament\Actions\Action;
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
                    ->formatStateUsing(fn (Post $record): string => static::statusLabelFor($record))
                    ->color(fn (Post $record): string => static::statusColorFor($record))
                    ->sortable()
                    ->description(fn (Post $record): ?string => $record->status === Post::STATUS_REJECTED && filled($record->rejection_reason) ? "Alasan: {$record->rejection_reason}" : null),

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
                    Action::make('preview')
                        ->label('Pratinjau')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (Post $record): string => route('posts.preview', $record->preview_token))
                        ->openUrlInNewTab(),
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

    /**
     * Artikel yang sudah terbit dengan perubahan menunggu tinjauan tetap
     * berstatus "Diterbitkan", tetapi diberi keterangan tambahan.
     */
    protected static function statusLabelFor(Post $record): string
    {
        if ($record->status === Post::STATUS_APPROVED
            && app(PostRevisionService::class)->hasPendingRevision($record)) {
            return 'Diterbitkan · Perubahan Ditinjau';
        }

        return static::statusLabel($record->status);
    }

    protected static function statusColorFor(Post $record): string
    {
        if ($record->status === Post::STATUS_APPROVED
            && app(PostRevisionService::class)->hasPendingRevision($record)) {
            return 'warning';
        }

        return static::statusColor($record->status);
    }
}
