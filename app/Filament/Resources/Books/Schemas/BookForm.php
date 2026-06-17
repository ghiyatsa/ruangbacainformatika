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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
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
                Grid::make([
                    'default' => 1,
                    'lg' => 3,
                ])
                    ->schema([
                        Group::make()
                            ->schema([
                                static::getIdentitasBukuSection(),
                                static::getDetailPublikasiSection(),
                            ])->columnSpan(['lg' => 2]),
                        Group::make()
                            ->schema([
                                static::getCoverBukuSection(),
                                static::getStatusVisibilitasSection(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    protected static function getIdentitasBukuSection(): Section
    {
        return Section::make('Identitas Buku')
            ->description('Isi informasi utama dan identitas resmi buku.')
            ->schema([
                TextInput::make('title')
                    ->label('Judul Buku')
                    ->required()
                    ->minLength(3)
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Book::generateSlugPreview($state)))
                    ->placeholder('Judul buku'),

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
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Deskripsi Singkat')
                    ->rows(3)
                    ->maxLength(1500)
                    ->columnSpanFull(),

                TextInput::make('isbn')
                    ->label('ISBN')
                    ->unique('books', 'isbn', ignoreRecord: true)
                    ->minLength(8)
                    ->maxLength(13)
                    ->placeholder('9786020000001')
                    ->helperText('Gunakan untuk buku biasa. Saat diisi, jalur ISSN disembunyikan.')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        $normalized = Isbn::normalize($state);

                        $set('isbn', $normalized);

                        if (filled($normalized)) {
                            $set('issn', null);
                        }
                    })
                    ->disabled(fn (Get $get): bool => ! blank($get('issn')))
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
                    ->maxLength(20)
                    ->placeholder('1234-5678')
                    ->helperText('Gunakan untuk jurnal atau serial. Saat diisi, jalur ISBN disembunyikan.')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        $normalized = filled($state) ? trim((string) $state) : null;

                        $set('issn', $normalized);

                        if (filled($normalized)) {
                            $set('isbn', null);
                        }
                    })
                    ->disabled(fn (Get $get): bool => ! blank($get('isbn')))
                    ->rule('regex:/^[0-9]{4}[-\s]?[0-9]{3}[0-9Xx]$/')
                    ->rules([
                        fn (Get $get, ?Book $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                            if (blank($value)) {
                                return;
                            }

                            $edition = trim((string) $get('edition'));
                            $pages = trim((string) $get('pages'));

                            $duplicateQuery = Book::query()
                                ->where('issn', (string) $value)
                                ->where('edition', $edition)
                                ->where('pages', $pages);

                            if ($record) {
                                $duplicateQuery->whereKeyNot($record->getKey());
                            }

                            if ($duplicateQuery->exists()) {
                                $fail('ISSN ini sudah dipakai untuk edisi dan halaman yang sama.');
                            }
                        },
                    ])
                    ->validationMessages([
                        'regex' => 'ISSN harus terdiri dari 8 karakter (contoh: 1234-5678 atau 1234-567X).',
                    ]),

                TextInput::make('ddc_code')
                    ->label('Kode DDC')
                    ->maxLength(20)
                    ->placeholder('000-999'),
                TextInput::make('language')
                    ->label('Bahasa')
                    ->default('Indonesia')
                    ->maxLength(30)
                    ->placeholder('Indonesia'),
            ])
            ->columns(2);
    }

    protected static function getDetailPublikasiSection(): Section
    {
        return Section::make('Detail Publikasi')
            ->description('Lengkapi detail penerbitan untuk mempermudah pencarian katalog.')
            ->schema([
                Select::make('publisher_id')
                    ->label('Penerbit')
                    ->relationship('publisher', 'name')
                    ->placeholder('Pilih Penerbit')
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
                    ->label('Edisi / Volume')
                    ->visible(fn (Get $get): bool => filled($get('issn')))
                    ->maxLength(255)
                    ->placeholder('Vol. 12 No. 2 atau Edisi Revisi'),

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
                    ->visible(fn (Get $get): bool => filled($get('issn')))
                    ->maxLength(255)
                    ->placeholder('250 atau 120-145')
                    ->helperText('Isi jumlah atau rentang halaman utama.'),

                Select::make('authors')
                    ->label('Penulis')
                    ->relationship('authors', 'name')
                    ->placeholder('Pilih Penulis')
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
                    ->placeholder('Pilih Kategori')
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
            ->columns(2);
    }

    protected static function getCoverBukuSection(): Section
    {
        return Section::make('Cover Buku')
            ->description('Unggah cover buku.')
            ->schema([
                FileUpload::make('cover_image')
                    ->hiddenLabel()
                    ->placeholder('Tarik & lepas berkas di sini atau Pilih Berkas')
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
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->extraAlpineAttributes([
                        'x-on:paste.window' => <<<'JS'
                            const files = ($event.clipboardData || window.clipboardData).files;
                            if (files && files.length > 0 && files[0].type.startsWith("image/")) {
                                const isInput = ["INPUT", "TEXTAREA"].includes($event.target.tagName) && !$event.target.readOnly;
                                const hasText = ($event.clipboardData || window.clipboardData).types.includes("text/plain");
                                if (isInput && hasText) {
                                    return;
                                }
                                $event.preventDefault();
                                const pond = FilePond.find($el.querySelector(".filepond--root") || $el.querySelector("input[type='file']"));
                                if (pond) {
                                    pond.addFile(files[0]);
                                }
                            }
                        JS,
                    ]),
            ])
            ->columns(1);
    }

    protected static function getStatusVisibilitasSection(): Section
    {
        return Section::make('Status & Visibilitas')
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
