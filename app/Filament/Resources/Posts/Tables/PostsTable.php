<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul')
            ->emptyStateHeading('Belum ada artikel')
            ->emptyStateDescription('Artikel akan tampil di sini.')
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->disk('public')
                    ->visibility('public')
                    ->square()
                    ->size(40)
                    ->defaultImageUrl(asset('images/article-placeholder.svg'))
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Judul Artikel')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Penulis')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::statusLabel($state))
                    ->color(fn (string $state): string => static::statusColor($state))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('categories.name')
                    ->label('Kategori')
                    ->badge()
                    ->separator(', ')
                    ->limitList(2)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tags.name')
                    ->label('Tag')
                    ->badge()
                    ->separator(', ')
                    ->limitList(2)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reviewedBy.name')
                    ->label('Peninjau')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),

                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Post::STATUS_DRAFT => 'Draf',
                        Post::STATUS_PENDING => 'Menunggu Peninjauan',
                        Post::STATUS_APPROVED => 'Diterbitkan',
                        Post::STATUS_REJECTED => 'Perlu Perbaikan',
                    ]),
                SelectFilter::make('categories')
                    ->label('Kategori')
                    ->relationship('categories', 'name'),
                SelectFilter::make('tags')
                    ->label('Tag')
                    ->relationship('tags', 'name'),
                SelectFilter::make('reviewed_by_user_id')
                    ->label('Peninjau')
                    ->relationship('reviewedBy', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('approve')
                        ->label('Terbitkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->hidden(fn (Post $record): bool => $record->status === Post::STATUS_APPROVED)
                        ->action(function (Post $record): void {
                            $record->update([
                                'status' => Post::STATUS_APPROVED,
                                'reviewed_by_user_id' => Auth::id(),
                                'reviewed_at' => now(),
                                'rejection_reason' => null,
                            ]);
                        }),
                    Action::make('reject')
                        ->label('Kembalikan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->hidden(fn (Post $record): bool => $record->status === Post::STATUS_REJECTED)
                        ->schema([
                            Textarea::make('rejection_reason')
                                ->label('Catatan untuk Penulis')
                                ->required()
                                ->rows(4)
                                ->maxLength(500),
                        ])
                        ->action(function (Post $record, array $data): void {
                            $record->update([
                                'status' => Post::STATUS_REJECTED,
                                'reviewed_by_user_id' => Auth::id(),
                                'reviewed_at' => now(),
                                'rejection_reason' => $data['rejection_reason'],
                            ]);
                        }),
                    Action::make('preview')
                        ->label('Pratinjau')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (Post $record): string => route('blog.preview', $record->preview_token))
                        ->openUrlInNewTab(),
                    ViewAction::make()
                        ->label('Lihat')
                        ->icon('heroicon-o-document-text'),
                    EditAction::make()
                        ->label('Tinjau')
                        ->icon('heroicon-o-pencil'),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-o-trash'),
                ])->label('Aksi'),
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
            Post::STATUS_PENDING => 'Menunggu Peninjauan',
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
