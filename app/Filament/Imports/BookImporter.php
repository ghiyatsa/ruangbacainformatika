<?php

namespace App\Filament\Imports;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Support\Library\BookItemBatchCreator;
use App\Support\Media\BookCoverImage;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class BookImporter extends Importer
{
    protected static ?string $model = Book::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('title')
                ->label('Judul Buku')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(function ($record, $state) {
                    $record->title = $state;
                    $record->slug = Str::slug($state);
                }),
            ImportColumn::make('isbn')
                ->label('ISBN')
                ->rules(['max:20']),
            ImportColumn::make('authors')
                ->label('Penulis')
                ->fillRecordUsing(function ($record, $state) {}),

            ImportColumn::make('categories')
                ->label('Kategori')
                ->fillRecordUsing(function ($record, $state) {}),

            ImportColumn::make('ddc_code')
                ->label('Kode DDC'),

            ImportColumn::make('description'),

            ImportColumn::make('cover_url')
                ->label('URL Cover Buku')
                ->rules(['nullable', 'url'])
                ->fillRecordUsing(function ($record, $state) {
                    if (blank($state)) {
                        return;
                    }

                    $baseName = $record->slug ?: $this->data['title'] ?? null;
                    $coverImage = app(BookCoverImage::class)->tryStoreFromUrl($state, baseName: $baseName);

                    if (filled($coverImage)) {
                        $record->cover_image = $coverImage;
                    }
                }),
            ImportColumn::make('edition'),
            ImportColumn::make('published_year')
                ->label('Tahun Terbit'),
            ImportColumn::make('pages')
                ->label('Jumlah Halaman')
                ->numeric(),
            ImportColumn::make('stock')
                ->label('Stok')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0'])
                ->fillRecordUsing(function ($record, $state) {}),
            ImportColumn::make('publisher')
                ->label('Penerbit')
                ->requiredMapping()
                ->fillRecordUsing(function ($record, $state) {
                    $publisher = Publisher::firstOrCreate(
                        ['name' => trim($state)],
                        ['slug' => Str::slug($state)]
                    );

                    $record->publisher_id = $publisher->id;
                }),
            ImportColumn::make('language')
                ->label('Bahasa')
                ->castStateUsing(fn (string $state) => $state ?: 'Indonesia'),

            ImportColumn::make('is_featured')
                ->boolean()
                ->rules(['boolean']),

            ImportColumn::make('is_published')
                ->boolean()
                ->rules(['boolean']),
        ];
    }

    protected function afterSave(): void
    {
        /** @var Book $book */
        $book = $this->record;

        $authorState = $this->data['authors'] ?? null;
        if (filled($authorState)) {
            $names = explode('|', $authorState);
            $ids = [];
            foreach ($names as $name) {
                $cleanName = trim($name);
                if (filled($cleanName)) {
                    $author = Author::firstOrCreate(
                        ['name' => $cleanName],
                        ['slug' => Str::slug($cleanName)]
                    );
                    $ids[] = $author->id;
                }
            }
            $book->authors()->sync($ids);
        }

        $categoryState = $this->data['categories'] ?? null;
        if (filled($categoryState)) {
            $names = explode('|', $categoryState);
            $ids = [];
            foreach ($names as $name) {
                $cleanName = trim($name);
                if (filled($cleanName)) {
                    $category = Category::firstOrCreate(
                        ['name' => $cleanName],
                        ['slug' => Str::slug($cleanName)]
                    );
                    $ids[] = $category->id;
                }
            }
            $book->categories()->sync($ids);
        }

        $stockState = $this->data['stock'] ?? null;
        if (filled($stockState)) {
            app(BookItemBatchCreator::class)->ensureStock($book, (int) $stockState);
        }
    }

    public function resolveRecord(): ?Model
    {
        $isbn = trim((string) ($this->data['isbn'] ?? ''));

        if (blank($isbn)) {
            return new Book;
        }

        return Book::firstOrNew([
            'isbn' => $isbn,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor buku selesai dan '.Number::format($import->successful_rows).' '.str('baris')->plural($import->successful_rows).' berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('baris')->plural($failedRowsCount).' gagal diimpor.';
        }

        return $body;
    }
}
