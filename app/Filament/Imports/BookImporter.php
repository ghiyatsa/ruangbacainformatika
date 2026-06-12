<?php

namespace App\Filament\Imports;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Services\BookItemBatchCreator;
use App\Support\Isbn;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

class BookImporter extends Importer
{
    protected static ?string $model = Book::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('title')
                ->label('Judul')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->helperText('Gunakan judul utama buku.')
                ->castStateUsing(fn ($state): string => static::normalizeRequiredString($state))
                ->fillRecordUsing(fn ($record, $state) => $record->title = $state),
            ImportColumn::make('subtitle')
                ->label('Subjudul')
                ->helperText('Kosongkan jika tidak ada subjudul.')
                ->rules(['nullable', 'max:255'])
                ->castStateUsing(fn ($state): ?string => static::normalizeOptionalString($state)),
            ImportColumn::make('isbn')
                ->label('ISBN')
                ->rules([
                    'nullable',
                    'max:20',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (blank($value)) {
                            return;
                        }

                        if (! Isbn::isValid((string) $value)) {
                            $fail('Kolom ISBN harus berisi 8 digit, ISBN-10, atau ISBN-13 yang valid.');
                        }
                    },
                ])
                ->helperText('Boleh dengan atau tanpa tanda hubung.')
                ->fillRecordUsing(function ($record, $state) {
                    $record->isbn = Isbn::normalize((string) $state);
                }),
            ImportColumn::make('authors')
                ->label('Penulis')
                ->helperText('Pisahkan beberapa penulis dengan tanda |')
                ->castStateUsing(fn ($state): ?string => static::normalizeOptionalString($state))
                ->fillRecordUsing(function ($record, $state) {}),

            ImportColumn::make('categories')
                ->label('Kategori')
                ->helperText('Pisahkan beberapa kategori dengan tanda |')
                ->castStateUsing(fn ($state): ?string => static::normalizeOptionalString($state))
                ->fillRecordUsing(function ($record, $state) {}),

            ImportColumn::make('ddc_code')
                ->label('Nomor DDC')
                ->castStateUsing(fn ($state): ?string => static::normalizeOptionalString($state)),

            ImportColumn::make('description')
                ->label('Deskripsi')
                ->castStateUsing(fn ($state): ?string => static::normalizeOptionalString($state)),

            ImportColumn::make('edition')
                ->label('Edisi')
                ->castStateUsing(fn ($state): ?string => static::normalizeOptionalString($state)),
            ImportColumn::make('published_year')
                ->label('Tahun Terbit')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:1000', 'max:2100']),
            ImportColumn::make('pages')
                ->label('Jumlah Halaman')
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('stock')
                ->label('Stok')
                ->numeric()
                ->helperText('Jika lebih besar dari stok saat ini, eksemplar akan ditambahkan sesuai kebutuhan.')
                ->rules(['nullable', 'integer', 'min:0'])
                ->fillRecordUsing(function ($record, $state) {}),
            ImportColumn::make('rack')
                ->label('Lokasi Rak')
                ->helperText('Dipakai untuk mengisi lokasi rak eksemplar, misalnya R-01-A.')
                ->rules(['nullable', 'max:255'])
                ->castStateUsing(fn ($state): ?string => static::normalizeOptionalString($state))
                ->fillRecordUsing(function ($record, $state) {}),
            ImportColumn::make('publisher')
                ->label('Penerbit')
                ->requiredMapping()
                ->helperText('Nama penerbit baru akan ditambahkan otomatis jika belum tersedia.')
                ->castStateUsing(fn ($state): string => static::normalizeRequiredString($state))
                ->fillRecordUsing(function ($record, $state) {
                    $publisher = Publisher::firstOrCreate(
                        ['name' => $state],
                        ['slug' => Publisher::generateSlug($state)]
                    );

                    $record->publisher_id = $publisher->id;
                }),
            ImportColumn::make('language')
                ->label('Bahasa')
                ->castStateUsing(fn ($state): string => static::normalizeOptionalString($state) ?? 'Indonesia'),

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
        $shelfLocation = static::normalizeOptionalString($this->data['rack'] ?? null);

        $authorIds = $this->resolveAuthorIds($this->data['authors'] ?? null);

        if ($authorIds !== []) {
            $book->authors()->sync($authorIds);
        }

        $categoryIds = $this->resolveCategoryIds($this->data['categories'] ?? null);

        if ($categoryIds !== []) {
            $book->categories()->sync($categoryIds);
        }

        $stockState = $this->data['stock'] ?? null;
        if (filled($stockState)) {
            app(BookItemBatchCreator::class)->ensureStock($book, (int) $stockState, $shelfLocation);
        }

        if ($shelfLocation !== null) {
            app(BookItemBatchCreator::class)->fillMissingShelfLocations($book, $shelfLocation);
        }
    }

    public function resolveRecord(): ?Model
    {
        $isbn = Isbn::normalize((string) ($this->data['isbn'] ?? '')) ?? '';

        if (blank($isbn)) {
            return new Book;
        }

        return Book::firstOrNew([
            'isbn' => $isbn,
        ]);
    }

    public function getValidationMessages(): array
    {
        return [
            'title.required' => 'Kolom judul wajib diisi.',
            'publisher.required' => 'Kolom penerbit wajib diisi.',
            'stock.integer' => 'Kolom stok harus berupa angka bulat.',
            'stock.min' => 'Kolom stok tidak boleh kurang dari 0.',
            'published_year.integer' => 'Kolom tahun terbit harus berupa angka bulat.',
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor buku selesai dan '.Number::format($import->successful_rows).' '.str('baris')->plural($import->successful_rows).' berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('baris')->plural($failedRowsCount).' gagal diimpor.';
        }

        return $body;
    }

    /**
     * @return array<int>
     */
    protected function resolveAuthorIds(mixed $state): array
    {
        return collect($this->splitList($state))
            ->map(function (string $name): int {
                $author = Author::firstOrCreate(
                    ['name' => $name],
                    ['slug' => Author::generateSlug($name)]
                );

                return $author->getKey();
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int>
     */
    protected function resolveCategoryIds(mixed $state): array
    {
        return collect($this->splitList($state))
            ->map(function (string $name): int {
                $category = Category::firstOrCreate(
                    ['name' => $name],
                    ['slug' => Category::generateSlug($name)]
                );

                return $category->getKey();
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function splitList(mixed $state): array
    {
        return collect(explode('|', (string) $state))
            ->map(fn (string $value): string => trim($value))
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    protected static function normalizeOptionalString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    protected static function normalizeRequiredString(mixed $value): string
    {
        return trim((string) $value);
    }
}
