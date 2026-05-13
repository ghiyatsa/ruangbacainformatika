<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Filament\Resources\Authors\Schemas\AuthorForm;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Publishers\Schemas\PublisherForm;
use App\Models\Author;
use App\Models\Category;
use App\Models\Publisher;
use App\Services\BookCoverImageService;
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
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Isi informasi utama buku sesuai data yang paling resmi dan mudah diverifikasi.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Buku')
                            ->required()
                            ->minLength(3)
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->placeholder('Masukkan judul buku')
                            ->helperText('Gunakan judul utama buku.'),

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
                            ->placeholder('Masukkan subjudul buku (jika ada)')
                            ->helperText('Isi jika ada subjudul resmi.')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi Singkat')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Ringkasan singkat isi buku.')
                            ->columnSpanFull(),

                        TextInput::make('isbn')
                            ->label('ISBN')
                            ->unique('books', 'isbn', ignoreRecord: true)
                            ->minLength(10)
                            ->maxLength(13)
                            ->placeholder('9786020000001')
                            ->helperText('Gunakan angka saja.')
                            ->rule('regex:/^[0-9]+$/')
                            ->validationMessages([
                                'regex' => 'ISBN hanya boleh berisi angka.',
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
                    ->description('Lengkapi data penerbitan agar katalog lebih rapi dan mudah ditelusuri.')
                    ->schema([
                        Select::make('publisher_id')
                            ->label('Penerbit')
                            ->relationship('publisher', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih atau tambah penerbit.')
                            ->createOptionForm(PublisherForm::optionFormSchema())
                            ->createOptionUsing(fn (array $data): int => static::createPublisher($data))
                            ->createOptionModalHeading('Tambah Penerbit')
                            ->createOptionAction(fn (Action $action): Action => $action
                                ->modalDescription('Tambahkan penerbit baru.')
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
                            ->placeholder('Edisi 1, Edisi Revisi, dan sebagainya')
                            ->helperText('Isi jika ada keterangan edisi.'),

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
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->placeholder('250')
                            ->helperText('Isi total halaman utama.'),

                        TextInput::make('language')
                            ->label('Bahasa')
                            ->default('Indonesia')
                            ->maxLength(30)
                            ->placeholder('Indonesia')
                            ->helperText('Contoh: Indonesia, Inggris.'),
                    ])
                    ->columns(2),

                Section::make('Konten & Media')
                    ->description('Unggah sampul agar buku lebih mudah dikenali pada panel admin dan katalog.')
                    ->schema([

                        FileUpload::make('cover_image')
                            ->hiddenLabel()
                            ->image()
                            ->directory('books/covers')
                            ->disk('public')
                            ->saveUploadedFileUsing(
                                fn (TemporaryUploadedFile $file, Get $get): string => app(BookCoverImageService::class)->storeFromUploadedFile($file, baseName: $get('slug') ?: $get('title')),
                            )
                            ->deleteUploadedFileUsing(function (string $file) {
                                if (Storage::disk('public')->exists($file)) {
                                    Storage::disk('public')->delete($file);
                                }
                            })
                            ->imageEditor()
                            ->imageEditorAspectRatioOptions([
                                '3:4',
                            ])
                            ->imageAspectRatio('3:4')
                            ->automaticallyCropImagesToAspectRatio()
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
                            ->helperText('Pilih atau tambah penulis.')
                            ->createOptionForm(AuthorForm::optionFormSchema())
                            ->createOptionUsing(fn (array $data): int => static::createAuthor($data))
                            ->createOptionModalHeading('Tambah Penulis')
                            ->createOptionAction(fn (Action $action): Action => $action
                                ->modalDescription('Tambahkan penulis baru.')
                                ->modalSubmitActionLabel('Simpan')),

                        Select::make('categories')
                            ->label('Kategori')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih atau tambah kategori.')
                            ->createOptionForm(CategoryForm::optionFormSchema())
                            ->createOptionUsing(fn (array $data): int => static::createCategory($data))
                            ->createOptionModalHeading('Tambah Kategori')
                            ->createOptionAction(fn (Action $action): Action => $action
                                ->modalDescription('Tambahkan kategori baru.')
                                ->modalSubmitActionLabel('Simpan')),
                    ])
                    ->columns(2),

                Section::make('Status & Visibilitas')
                    ->description('Tentukan bagaimana buku ini tampil dan digunakan dalam sistem.')
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
                            ->helperText('Diperbarui otomatis oleh sistem.'),
                    ]),
            ]);
    }

    protected static function createPublisher(array $data): int
    {
        $publisher = Publisher::query()->create([
            'name' => $data['name'],
            'slug' => static::generateUniqueSlug($data['name'], Publisher::class),
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
            'slug' => static::generateUniqueSlug($data['name'], Publisher::class, $publisher?->getKey()),
            'city' => $data['city'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }

    protected static function createAuthor(array $data): int
    {
        $author = Author::query()->create([
            'name' => $data['name'],
            'slug' => static::generateUniqueSlug($data['name'], Author::class),
            'email' => $data['email'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);

        return $author->getKey();
    }

    protected static function createCategory(array $data): int
    {
        $category = Category::query()->create([
            'name' => $data['name'],
            'slug' => static::generateUniqueSlug($data['name'], Category::class),
            'description' => $data['description'] ?? null,
        ]);

        return $category->getKey();
    }

    protected static function generateUniqueSlug(string $value, string $modelClass, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'item';
        $slug = $baseSlug;
        $suffix = 2;

        while (static::slugExists($modelClass, $slug, $ignoreId)) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    protected static function slugExists(string $modelClass, string $slug, ?int $ignoreId = null): bool
    {
        $query = $modelClass::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
