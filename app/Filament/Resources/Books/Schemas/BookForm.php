<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Filament\Resources\Authors\Schemas\AuthorForm;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Publishers\Schemas\PublisherForm;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Services\BookCoverImageService;
use App\Support\Isbn;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Isi data utama buku berdasarkan sumber yang paling jelas.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Buku')
                            ->required()
                            ->minLength(3)
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Book::generateSlugPreview($state)))
                            ->placeholder('Judul buku')
                            ->helperText('Gunakan judul utama pada buku.'),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('books', 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('subtitle')
                            ->label('Subjudul')
                            ->maxLength(255)
                            ->placeholder('Subjudul jika ada')
                            ->helperText('Isi jika tersedia.')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi Singkat')
                            ->rows(3)
                            ->maxLength(1500)
                            ->helperText('Ringkasan singkat isi buku.')
                            ->columnSpanFull(),

                        TextInput::make('isbn')
                            ->label('ISBN')
                            ->unique('books', 'isbn', ignoreRecord: true)
                            ->minLength(8)
                            ->maxLength(13)
                            ->placeholder('9786020000001')
                            ->helperText('Gunakan ISBN 8 digit, ISBN-10, atau ISBN-13 tanpa spasi.')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('isbn', Isbn::normalize($state)))
                            ->rule('regex:/^(?:[0-9]{8}|[0-9]{10}|[0-9]{13}|[0-9]{9}X)$/i')
                            ->rules([
                                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                    if (blank($value)) {
                                        return;
                                    }

                                    if (! Isbn::hasAcceptedFormat((string) $value)) {
                                        $fail('ISBN harus berupa 8 digit, ISBN-10, atau ISBN-13 tanpa spasi.');
                                    }
                                },
                            ])
                            ->validationMessages([
                                'regex' => 'ISBN harus 8, 10, atau 13 karakter tanpa spasi. Gunakan X hanya di akhir ISBN-10.',
                            ]),

                        TextInput::make('issn')
                            ->label('ISSN')
                            ->unique('books', 'issn', ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('1234-5678')
                            ->helperText('Isi jika tersedia.')
                            ->rule('regex:/^[0-9\\-\\s]+$/')
                            ->validationMessages([
                                'regex' => 'ISSN hanya boleh berisi angka, spasi, dan tanda hubung.',
                            ]),

                        TextInput::make('ddc_code')
                            ->label('Kode DDC')
                            ->helperText('Isi jika tersedia.')
                            ->maxLength(20)
                            ->placeholder('000-999'),
                    ])
                    ->columns(2),

                Section::make('Detail Publikasi')
                    ->description('Lengkapi data terbit agar katalog lebih rapi dan mudah ditelusuri.')
                    ->schema([
                        Select::make('publisher_id')
                            ->label('Penerbit')
                            ->relationship('publisher', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih penerbit atau tambahkan data baru.')
                            ->createOptionForm(PublisherForm::optionFormSchema())
                            ->createOptionUsing(fn (array $data): int => static::createPublisher($data))
                            ->createOptionModalHeading('Tambah Penerbit')
                            ->createOptionAction(fn (Action $action): Action => $action
                                ->modalDescription('Tambahkan data penerbit baru.')
                                ->modalSubmitActionLabel('Simpan'))
                            ->editOptionForm(PublisherForm::optionFormSchema())
                            ->updateOptionUsing(function (array $data, Schema $schema): void {
                                static::updatePublisher($schema, $data);
                            })
                            ->editOptionModalHeading('Ubah Penerbit')
                            ->editOptionAction(fn (Action $action): Action => $action
                                ->modalDescription('Perbarui data penerbit.')
                                ->modalSubmitActionLabel('Simpan')),

                        TextInput::make('edition')
                            ->label('Edisi')
                            ->maxLength(255)
                            ->placeholder('Edisi 1, Edisi Revisi, dan lain-lain')
                            ->helperText('Isi jika tersedia.'),

                        TextInput::make('published_year')
                            ->label('Tahun Terbit')
                            ->numeric()
                            ->integer()
                            ->minValue(1000)
                            ->maxValue(now()->year)
                            ->placeholder((string) now()->year)
                            ->helperText('Gunakan 4 digit tahun.'),

                        TextInput::make('pages')
                            ->label('Jumlah Halaman')
                            ->maxLength(255)
                            ->placeholder('250')
                            ->helperText('Isi jumlah halaman utama.'),

                        TextInput::make('language')
                            ->label('Bahasa')
                            ->default('Indonesia')
                            ->maxLength(30)
                            ->placeholder('Indonesia')
                            ->helperText('Contoh: Indonesia atau Inggris.'),
                    ])
                    ->columns(2),

                Section::make('Konten & Media')
                    ->description('Unggah sampul agar buku lebih mudah dikenali.')
                    ->schema([

                        FileUpload::make('cover_image')
                            ->hiddenLabel()
                            ->image()
                            ->directory('books/covers')
                            ->disk('public')
                            ->imagePreviewHeight('320')
                            ->saveUploadedFileUsing(
                                fn (TemporaryUploadedFile $file, Get $get): string => app(BookCoverImageService::class)->storeFromUploadedFile($file, baseName: $get('slug') ?: $get('title')),
                            )
                            ->deleteUploadedFileUsing(function (string $file) {
                                if (Storage::disk('public')->exists($file)) {
                                    Storage::disk('public')->delete($file);
                                }
                            })
                            ->imageEditor()
                            ->maxSize(2048)
                            ->helperText('JPG, PNG, atau WEBP. Maksimal 2 MB.')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                    ])
                    ->columns(1),

                Section::make('Relasi')
                    ->description('Hubungkan buku dengan data pendukung yang relevan.')
                    ->schema([
                        Select::make('authors')
                            ->label('Penulis')
                            ->relationship('authors', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih penulis atau tambahkan data baru.')
                            ->createOptionForm(AuthorForm::optionFormSchema())
                            ->createOptionUsing(fn (array $data): int => static::createAuthor($data))
                            ->createOptionModalHeading('Tambah Penulis')
                            ->createOptionAction(fn (Action $action): Action => $action
                                ->modalDescription('Tambahkan data penulis baru.')
                                ->modalSubmitActionLabel('Simpan')),

                        Select::make('categories')
                            ->label('Kategori')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih kategori atau tambahkan data baru.')
                            ->createOptionForm(CategoryForm::optionFormSchema())
                            ->createOptionUsing(fn (array $data): int => static::createCategory($data))
                            ->createOptionModalHeading('Tambah Kategori')
                            ->createOptionAction(fn (Action $action): Action => $action
                                ->modalDescription('Tambahkan kategori baru.')
                                ->modalSubmitActionLabel('Simpan')),
                    ])
                    ->columns(2),

                Section::make('Status & Visibilitas')
                    ->description('Atur visibilitas dan penggunaan buku.')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Dipublikasikan')
                            ->default(false),

                        Toggle::make('is_featured')
                            ->label('Buku Unggulan')
                            ->default(false),

                        Toggle::make('is_borrowable')
                            ->label('Boleh Dipinjam')
                            ->default(true),

                        Placeholder::make('view_count')
                            ->label('Jumlah Dilihat')
                            ->content(fn ($record): string => number_format((int) ($record?->view_count ?? 0)))
                            ->helperText('Diperbarui otomatis.'),
                    ]),
            ]);
    }

    protected static function createPublisher(array $data): int
    {
        $publisher = Publisher::query()->create([
            'name' => $data['name'],
            'slug' => Publisher::generateUniqueSlug($data['name']),
            'city' => $data['city'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return $publisher->getKey();
    }

    protected static function updatePublisher(Schema $schema, array $data): void
    {
        /** @var Publisher|null $publisher */
        $publisher = $schema->getRecord();

        $publisher?->update([
            'name' => $data['name'],
            'slug' => Publisher::generateUniqueSlug($data['name'], $publisher?->getKey()),
            'city' => $data['city'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }

    protected static function createAuthor(array $data): int
    {
        $author = Author::query()->create([
            'name' => $data['name'],
            'slug' => Author::generateUniqueSlug($data['name']),
            'email' => $data['email'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);

        return $author->getKey();
    }

    protected static function createCategory(array $data): int
    {
        $category = Category::query()->create([
            'name' => $data['name'],
            'slug' => Category::generateUniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return $category->getKey();
    }
}
