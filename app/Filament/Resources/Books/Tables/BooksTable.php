<?php

namespace App\Filament\Resources\Books\Tables;

use App\Filament\Imports\BookImporter;
use App\Models\Book;
use App\Models\BookItem;
use App\Services\BookCoverImageService;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul, ISBN, atau penerbit')
            ->emptyStateHeading('Belum ada data buku')
            ->emptyStateDescription('Daftar buku akan muncul di sini.')
            ->emptyStateIcon(Heroicon::OutlinedBookOpen)
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->alignCenter()
                    ->defaultImageUrl(app(BookCoverImageService::class)->getDefaultCoverUrl())
                    ->extraImgAttributes([
                        'class' => 'object-contain bg-white p-1',
                    ])
                    ->disk('public'),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->search($search))
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): string => collect([
                        $record->isbn ? "ISBN: {$record->isbn}" : null,
                        $record->issn ? "ISSN: {$record->issn}" : null,
                    ])->filter()->join(' | ') ?: '-')
                    ->wrap(),

                TextColumn::make('subtitle')
                    ->label('Subjudul')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('categories.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('success')
                    ->limitList(2)
                    ->listWithLineBreaks()
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('publisher.name')
                    ->label('Penerbit')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('published_year')
                    ->label('Tahun')
                    ->sortable(),

                TextColumn::make('available_stock')
                    ->label('Stok')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match (true) {
                        $state == 0 => 'danger',
                        $state <= 2 => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('pages')
                    ->label('Halaman')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_published')
                    ->label('Dipublikasikan')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),

                IconColumn::make('is_borrowable')
                    ->label('Boleh Dipinjam')
                    ->boolean(),

                TextColumn::make('view_count')
                    ->label('Dilihat')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Publikasi')
                    ->placeholder('Semua')
                    ->trueLabel('Dipublikasikan')
                    ->falseLabel('Draf'),
                TernaryFilter::make('is_featured')
                    ->label('Unggulan')
                    ->placeholder('Semua')
                    ->trueLabel('Unggulan')
                    ->falseLabel('Bukan unggulan'),
                TernaryFilter::make('is_borrowable')
                    ->label('Peminjaman')
                    ->placeholder('Semua')
                    ->trueLabel('Boleh dipinjam')
                    ->falseLabel('Tidak boleh dipinjam'),
                Filter::make('out_of_stock')
                    ->label('Hanya stok habis')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave(
                        'items',
                        fn (Builder $query): Builder => $query->where('status', 'available'),
                    )),
                Filter::make('without_cover')
                    ->label('Tanpa sampul')
                    ->query(fn (Builder $query): Builder => $query->whereNull('cover_image')),
                SelectFilter::make('publisher')
                    ->label('Penerbit')
                    ->relationship(
                        'publisher',
                        'name',
                        fn (Builder $query): Builder => $query->whereNotNull('name')->orderBy('name'),
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('categories')
                    ->label('Kategori')
                    ->relationship(
                        'categories',
                        'name',
                        fn (Builder $query): Builder => $query->whereNotNull('name')->orderBy('name'),
                    )
                    ->multiple()
                    ->preload(),
                SelectFilter::make('authors')
                    ->label('Penulis')
                    ->relationship(
                        'authors',
                        'name',
                        fn (Builder $query): Builder => $query->whereNotNull('name')->orderBy('name'),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('published_year')
                    ->label('Tahun Terbit')
                    ->options(fn (): array => Book::query()
                        ->whereNotNull('published_year')
                        ->orderByDesc('published_year')
                        ->pluck('published_year', 'published_year')
                        ->mapWithKeys(fn ($year): array => [(string) $year => (string) $year])
                        ->all()),
                SelectFilter::make('shelf_location')
                    ->label('Lokasi Rak')
                    ->options(fn (): array => BookItem::query()
                        ->whereNotNull('shelf_location')
                        ->where('shelf_location', '!=', '')
                        ->orderBy('shelf_location')
                        ->distinct()
                        ->pluck('shelf_location', 'shelf_location')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $value): Builder => $query->whereHas(
                            'items',
                            fn (Builder $q): Builder => $q->where('shelf_location', $value)
                        )
                    )),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),
                    EditAction::make()
                        ->label('Ubah'),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->before(function (DeleteAction $action, Book $record): void {
                            if (! $reason = $record->deletionBlockedReason()) {
                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('Buku belum bisa dihapus')
                                ->body($reason)
                                ->send();

                            $action->halt();
                        }),
                ])
                    ->label('Aksi'),
            ])
            ->toolbarActions([
                ImportAction::make('importBooks')
                    ->importer(BookImporter::class)
                    ->label('Impor')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->color('info'),
                BulkActionGroup::make([
                    BulkAction::make('feature_selected')
                        ->label('Tandai Unggulan')
                        ->icon(Heroicon::OutlinedStar)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Book $record) => $record->update(['is_featured' => true])))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('unfeature_selected')
                        ->label('Batalkan Unggulan')
                        ->icon(Heroicon::OutlinedArchiveBoxXMark)
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Book $record) => $record->update(['is_featured' => false])))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('mark_non_borrowable')
                        ->label('Nonaktifkan Pinjam')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Book $record) => $record->update(['is_borrowable' => false])))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('mark_borrowable')
                        ->label('Aktifkan Pinjam')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Book $record) => $record->update(['is_borrowable' => true])))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('publish_selected')
                        ->label('Publikasikan')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Book $record) => $record->update(['is_published' => true])))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('unpublish_selected')
                        ->label('Batalkan Publikasi')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Book $record) => $record->update(['is_published' => false])))
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            /** @var Book|null $blockedRecord */
                            $blockedRecord = $records->first(fn (Book $record): bool => $record->deletionBlockedReason() !== null);

                            if (! $blockedRecord) {
                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('Sebagian buku belum bisa dihapus')
                                ->body($blockedRecord->deletionBlockedReason() ?? 'Masih ada data yang terkait dengan buku terpilih.')
                                ->send();

                            $action->halt();
                        }),
                ]),
            ]);
    }
}
