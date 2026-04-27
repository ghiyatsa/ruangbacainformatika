<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Support\Media\BookCoverImage;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
                    ->description('Data utama buku')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Buku')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->placeholder('Masukkan judul buku'),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique('books', 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Deskripsi Singkat')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        TextInput::make('isbn')
                            ->label('ISBN')
                            ->unique('books', 'isbn', ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('978-xxx-xxx-xxx'),

                        TextInput::make('ddc_code')
                            ->label('DDC Code (Dewey Decimal)')
                            ->maxLength(20)
                            ->placeholder('000-999'),
                    ])
                    ->columns(2),

                Section::make('Detail Publikasi')
                    ->description('Informasi tentang publikasi dan format buku')
                    ->schema([
                        Select::make('publisher_id')
                            ->label('Penerbit')
                            ->relationship('publisher', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('edition')
                            ->label('Edisi')
                            ->maxLength(255)
                            ->placeholder('Edisi 1, Edisi Revisi, dll'),

                        TextInput::make('published_year')
                            ->label('Tahun Terbit')
                            ->numeric()
                            ->minValue(1000)
                            ->maxValue(now()->year),

                        TextInput::make('pages')
                            ->label('Jumlah Halaman')
                            ->numeric()
                            ->minValue(1),

                        TextInput::make('language')
                            ->label('Bahasa')
                            ->default('Indonesia')
                            ->maxLength(30),
                    ])
                    ->columns(2),

                Section::make('Konten & Media')
                    ->description('Deskripsi dan gambar sampul buku')
                    ->schema([

                        TextInput::make('cover_url')
                            ->hiddenLabel()
                            ->placeholder('Tempel URL gambar di sini...')
                            ->url()
                            ->suffixAction(
                                Action::make('fetch_image')
                                    ->icon(Heroicon::OutlinedArrowDownCircle)
                                    ->action(function (Get $get, Set $set): void {
                                        $url = $get('cover_url');

                                        if (blank($url)) {
                                            Notification::make()
                                                ->title('URL Cover Buku Kosong')
                                                ->body('Silahkan masukkan URL gambar cover buku terlebih dahulu.')
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        try {
                                            $set('cover_image', app(BookCoverImage::class)->storeFromUrl($url, baseName: $get('slug') ?: $get('title')));

                                            Notification::make()
                                                ->title('Gambar berhasil diunduh!')
                                                ->success()
                                                ->send();
                                        } catch (\Exception $exception) {
                                            Notification::make()
                                                ->title('Gagal mengunduh gambar')
                                                ->body('Detail Error: '.$exception->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    }),
                            ),
                        FileUpload::make('cover_image')
                            ->hiddenLabel()
                            ->image()
                            ->directory('books/covers')
                            ->disk('public')
                            ->saveUploadedFileUsing(
                                fn (TemporaryUploadedFile $file, Get $get): string => app(BookCoverImage::class)->storeFromUploadedFile($file, baseName: $get('slug') ?: $get('title')),
                            )
                            ->deleteUploadedFileUsing(function (string $file) {
                                if (Storage::disk('public')->exists($file)) {
                                    Storage::disk('public')->delete($file);
                                }
                            })
                            ->imageEditor()
                            ->maxSize(2048)
                            ->helperText('Jika cover kosong atau URL gagal diunduh, sistem akan memakai cover default.')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                    ])
                    ->columns(1),

                Section::make('Relasi')
                    ->description('Hubungan dengan penulis dan kategori')
                    ->schema([
                        Select::make('authors')
                            ->label('Penulis')
                            ->relationship('authors', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Select::make('categories')
                            ->label('Kategori')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Status & Visibilitas')
                    ->description('Pengaturan publikasi dan fitur buku')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Dipublikasikan')
                            ->default(false),

                        Toggle::make('is_featured')
                            ->label('Buku Unggulan')
                            ->default(false),

                        TextInput::make('view_count')
                            ->label('Jumlah Viewer')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3),
            ]);
    }
}
