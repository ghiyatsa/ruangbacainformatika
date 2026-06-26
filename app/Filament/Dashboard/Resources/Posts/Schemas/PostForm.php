<?php

namespace App\Filament\Dashboard\Resources\Posts\Schemas;

use App\Models\Post;
use App\Services\PostThumbnailImageService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PostForm
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
                        Section::make('Artikel Perlu Perbaikan (Ditolak)')
                            ->description(fn (?Post $record): string => 'Catatan Peninjau: '.($record?->rejection_reason ?? '').'. Silakan perbaiki artikel sesuai catatan tersebut, lalu ubah status di kanan menjadi "Ajukan Peninjauan" untuk mengirimkan kembali.')
                            ->icon(Heroicon::OutlinedExclamationTriangle)
                            ->iconColor('danger')
                            ->visible(fn (?Post $record): bool => $record !== null && $record->status === Post::STATUS_REJECTED && filled($record->rejection_reason))
                            ->columnSpanFull()
                            ->schema([]),

                        Section::make('Artikel Telah Diterbitkan')
                            ->description('Artikel ini sudah aktif dan dapat dibaca oleh publik. Penting: Jika Anda menyimpan perubahan pada artikel ini, statusnya akan ditarik kembali menjadi "Dalam Peninjauan" (unpublish) untuk ditinjau ulang oleh Admin/Staff.')
                            ->icon(Heroicon::OutlinedCheckCircle)
                            ->iconColor('success')
                            ->visible(fn (?Post $record): bool => $record !== null && $record->status === Post::STATUS_APPROVED)
                            ->columnSpanFull()
                            ->schema([]),

                        Section::make('Artikel Sedang Ditinjau')
                            ->description('Artikel ini sedang dalam antrean peninjauan oleh Admin/Staff dan belum diterbitkan. Anda masih dapat mengubah isi artikel ini sebelum disetujui, namun artikel akan tetap berada dalam status peninjauan.')
                            ->icon(Heroicon::OutlinedClock)
                            ->iconColor('warning')
                            ->visible(fn (?Post $record): bool => $record !== null && $record->status === Post::STATUS_PENDING)
                            ->columnSpanFull()
                            ->schema([]),

                        Group::make()
                            ->schema([
                                Section::make('Media Utama')
                                    ->schema([
                                        FileUpload::make('cover_image')
                                            ->label('Foto Sampul Artikel')
                                            ->placeholder('Tarik & lepas gambar di sini atau Pilih Berkas')
                                            ->image()
                                            ->disk('public')
                                            ->directory('posts/covers')
                                            ->visibility('public')
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->imagePreviewHeight('320')
                                            ->saveUploadedFileUsing(
                                                fn (TemporaryUploadedFile $file, Get $get): string => app(PostThumbnailImageService::class)->storeFromUploadedFile($file, baseName: $get('slug') ?: $get('title')),
                                            )
                                            ->deleteUploadedFileUsing(function (string $file) {
                                                if (
                                                    Storage::disk('public')->exists($file)
                                                ) {
                                                    Storage::disk('public')->delete($file);
                                                }
                                            }),
                                    ]),

                                Section::make('Isi & Redaksi')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul Artikel')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Post::generateSlugPreview($state))),

                                        TextInput::make('slug')
                                            ->label('Slug URL')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->unique(table: 'posts', column: 'slug', ignoreRecord: true),

                                        RichEditor::make('content')
                                            ->label('Badan Artikel')
                                            ->required()
                                            ->columnSpanFull()
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('posts/attachments')
                                            ->fileAttachmentsVisibility('public')
                                            ->resizableImages()
                                            ->toolbarButtons([
                                                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                                                [
                                                    ToolbarButtonGroup::make('Heading', ['h2', 'h3'])
                                                        ->icon('heroicon-o-hashtag'),
                                                ],
                                                [
                                                    ToolbarButtonGroup::make('Alignment', ['alignStart', 'alignCenter', 'alignEnd'])
                                                        ->icon('heroicon-o-bars-3-bottom-left'),
                                                ],
                                                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                                                ['table', 'attachFiles'],
                                                ['undo', 'redo'],
                                            ])
                                            ->live(onBlur: true),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(['lg' => 2]),

                        Group::make()
                            ->schema([
                                Section::make('Klasifikasi')
                                    ->schema([
                                        Placeholder::make('author_placeholder')
                                            ->label('Penulis')
                                            ->content(fn (): string => auth()->user()?->name ?? '-'),

                                        Select::make('categories')
                                            ->label('Kategori')
                                            ->relationship('categories', 'name')
                                            ->placeholder('Pilih Kategori')
                                            ->multiple()
                                            ->preload()
                                            ->rules(['array'])
                                            ->nestedRecursiveRules(['exists:post_categories,id'])
                                            ->searchable(),

                                        Select::make('tags')
                                            ->label('Tag')
                                            ->relationship('tags', 'name')
                                            ->placeholder('Pilih Tag')
                                            ->multiple()
                                            ->preload()
                                            ->rules(['array'])
                                            ->nestedRecursiveRules(['exists:post_tags,id'])
                                            ->searchable(),
                                    ]),

                                Section::make('Penerbitan')
                                    ->schema([
                                        Toggle::make('allow_comments')
                                            ->label('Izinkan Komentar')
                                            ->default(true),

                                        Placeholder::make('review_note')
                                            ->label('Catatan Review Sebelumnya')
                                            ->content(fn (?Post $record): string => $record?->rejection_reason ?? '-')
                                            ->visible(fn (?Post $record): bool => filled($record?->rejection_reason)),

                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                Post::STATUS_DRAFT => 'Draf',
                                                Post::STATUS_PENDING => 'Ajukan Peninjauan',
                                            ])
                                            ->required()
                                            ->default(Post::STATUS_DRAFT),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
