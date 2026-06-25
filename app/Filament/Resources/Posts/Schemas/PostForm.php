<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Services\PostThumbnailImageService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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
                                            })
                                            ->disabled(fn ($record): bool => $record !== null && $record->user_id !== auth()->id()),
                                    ]),

                                Section::make('Isi & Redaksi')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul Artikel')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Post::generateSlugPreview($state)))
                                            ->disabled(fn ($record): bool => $record !== null && $record->user_id !== auth()->id()),

                                        TextInput::make('slug')
                                            ->label('Slug URL')
                                            ->required()
                                            ->unique(table: 'posts', column: 'slug', ignoreRecord: true)
                                            ->disabled(fn ($record): bool => $record !== null && $record->user_id !== auth()->id()),

                                        RichEditor::make('content')
                                            ->label('Badan Artikel')
                                            ->required()
                                            ->columnSpanFull()
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('posts/attachments')
                                            ->fileAttachmentsVisibility('public')
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
                                            ->live(onBlur: true)
                                            ->disabled(fn ($record): bool => $record !== null && $record->user_id !== auth()->id()),
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
                                            ->content(fn (?Post $record): string => $record?->user?->name ?? auth()->user()?->name ?? '-'),

                                        Hidden::make('user_id')
                                            ->default(auth()->id()),

                                        Select::make('categories')
                                            ->label('Kategori')
                                            ->relationship('categories', 'name')
                                            ->placeholder('Pilih Kategori')
                                            ->multiple()
                                            ->preload()
                                            ->createOptionForm([
                                                TextInput::make('name')->required(),
                                                Textarea::make('description'),
                                            ])
                                            ->createOptionUsing(fn (array $data): int => static::createCategory($data)),

                                        TagsInput::make('tags')
                                            ->label('Tag')
                                            ->placeholder('Pilih atau ketik label / tag baru')
                                            ->suggestions(fn () => PostTag::pluck('name')->toArray())
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function (TagsInput $component, ?Post $record) {
                                                if ($record === null || ! $record->exists) {
                                                    return;
                                                }
                                                $component->state($record->tags()->pluck('name')->toArray());
                                            })
                                            ->saveRelationshipsUsing(function (?Post $record, array $state) {
                                                if ($record === null) {
                                                    return;
                                                }
                                                $tagIds = [];
                                                foreach ($state as $tagName) {
                                                    $tag = PostTag::findOrCreateByName($tagName);
                                                    $tagIds[] = $tag->getKey();
                                                }
                                                $record->tags()->sync($tagIds);
                                            }),
                                    ]),

                                Section::make('Peninjauan & Moderasi')
                                    ->schema([
                                        Toggle::make('allow_comments')
                                            ->label('Izinkan Komentar')
                                            ->default(true)
                                            ->disabled(fn ($record): bool => $record !== null && $record->user_id !== auth()->id()),

                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                Post::STATUS_DRAFT => 'Draf',
                                                Post::STATUS_PENDING => 'Menunggu Moderasi',
                                                Post::STATUS_APPROVED => 'Disetujui & Terbit',
                                                Post::STATUS_REJECTED => 'Ditolak / Perlu Revisi',
                                            ])
                                            ->required()
                                            ->live(),

                                        Textarea::make('rejection_reason')
                                            ->label('Catatan Peninjau')
                                            ->required()
                                            ->visible(fn (Get $get): bool => $get('status') === Post::STATUS_REJECTED),

                                        Placeholder::make('reviewed_by')
                                            ->label('Peninjau')
                                            ->content(fn (?Post $record): string => $record?->reviewedBy?->name ?? auth()->user()?->name ?? '-'),

                                        Placeholder::make('reviewed_at')
                                            ->label('Waktu Tinjau')
                                            ->content(fn (?Post $record): string => $record?->reviewed_at?->translatedFormat('d M Y H:i') ?? '-'),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ])->columnSpanFull(),
            ]);
    }

    protected static function createCategory(array $data): int
    {
        $category = PostCategory::query()->create([
            'name' => $data['name'],
            'slug' => PostCategory::generateUniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return $category->getKey();
    }
}
