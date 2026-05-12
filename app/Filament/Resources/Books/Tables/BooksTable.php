<?php

namespace App\Filament\Resources\Books\Tables;

use App\Filament\Imports\BookImporter;
use App\Models\Book;
use App\Services\BookCoverImageService;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
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
            ->emptyStateDescription('Tambahkan buku baru atau impor koleksi agar katalog mulai terisi.')
            ->emptyStateIcon(Heroicon::OutlinedBookOpen)
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->alignCenter()
                    ->defaultImageUrl(app(BookCoverImageService::class)->getDefaultCoverUrl())
                    ->disk('public'),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
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
                    ->label('Dipublikasi')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),

                IconColumn::make('is_borrowable')
                    ->label('Boleh Dipinjam')
                    ->boolean(),

                TextColumn::make('view_count')
                    ->label('Views')
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
                    ->label('Status Publikasi')
                    ->placeholder('Semua status')
                    ->trueLabel('Dipublikasikan')
                    ->falseLabel('Draf'),
                TernaryFilter::make('is_featured')
                    ->label('Sorotan')
                    ->placeholder('Semua buku')
                    ->trueLabel('Hanya sorotan')
                    ->falseLabel('Bukan sorotan'),
                TernaryFilter::make('is_borrowable')
                    ->label('Status Peminjaman')
                    ->placeholder('Semua buku')
                    ->trueLabel('Boleh dipinjam')
                    ->falseLabel('Tidak boleh dipinjam'),
                Filter::make('out_of_stock')
                    ->label('Stok habis')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave(
                        'items',
                        fn (Builder $query): Builder => $query->where('status', 'available'),
                    )),
                Filter::make('without_cover')
                    ->label('Tanpa cover')
                    ->query(fn (Builder $query): Builder => $query->whereNull('cover_image')),
                SelectFilter::make('publisher')
                    ->label('Penerbit')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('categories')
                    ->label('Kategori')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('published_year')
                    ->label('Tahun Terbit')
                    ->options(fn (): array => Book::query()
                        ->orderByDesc('published_year')
                        ->pluck('published_year', 'published_year')
                        ->all()),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Ubah Buku'),
                    DeleteAction::make(),
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
                        ->label('Tandai Sebagai Unggulan')
                        ->icon(Heroicon::OutlinedStar)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Book $record) => $record->update(['is_featured' => true])))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('unfeature_selected')
                        ->label('Hapus Dari Unggulan')
                        ->icon(Heroicon::OutlinedArchiveBoxXMark)
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Book $record) => $record->update(['is_featured' => false])))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('mark_non_borrowable')
                        ->label('Tandai Tidak Boleh Dipinjam')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Book $record) => $record->update(['is_borrowable' => false])))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('mark_borrowable')
                        ->label('Tandai Boleh Dipinjam')
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
                        ->label('Batal Publikasikan')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Book $record) => $record->update(['is_published' => false])))
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
