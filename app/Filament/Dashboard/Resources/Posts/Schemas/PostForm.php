<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\Posts\Schemas;

use App\Filament\Concerns\ManagesPostRevisionForm;
use App\Models\Post;
use App\Models\PostTag;
use App\Services\Post\PostRevisionService;
use App\Services\PostThumbnailImageService;
use Filament\Forms\Components\FileUpload;
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
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PostForm
{
    use ManagesPostRevisionForm;

    /**
     * Artikel yang sudah terbit tidak ditimpa langsung; perubahan penulis
     * disimpan sebagai revisi sampai disetujui peninjau.
     */
    protected static function shouldStageRevision(?Post $record): bool
    {
        return $record !== null && $record->status === Post::STATUS_APPROVED;
    }

    /**
     * Catatan penolakan terbaru, baik dari artikel maupun dari revisi.
     */
    protected static function revisionRejectionReason(?Post $record): ?string
    {
        if ($record === null) {
            return null;
        }

        return app(PostRevisionService::class)->workingRevision($record)?->rejection_reason;
    }

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
                            ->description(fn (?Post $record): string => 'Catatan: '.(static::revisionRejectionReason($record) ?? $record?->rejection_reason ?? ''))
                            ->icon(Heroicon::OutlinedExclamationTriangle)
                            ->iconColor('danger')
                            ->visible(fn (?Post $record): bool => filled(static::revisionRejectionReason($record)) || ($record !== null && $record->status === Post::STATUS_REJECTED && filled($record->rejection_reason)))
                            ->columnSpanFull()
                            ->schema([]),

                        Section::make('Artikel Telah Diterbitkan')
                            ->description('Perubahan Anda akan ditinjau dulu. Artikel versi sekarang tetap tampil sampai perubahan disetujui.')
                            ->icon(Heroicon::OutlinedCheckCircle)
                            ->iconColor('success')
                            ->visible(fn (?Post $record): bool => $record !== null && $record->status === Post::STATUS_APPROVED)
                            ->columnSpanFull()
                            ->schema([]),

                        Section::make('Artikel Sedang Ditinjau')
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

                                        Textarea::make('summary')
                                            ->label('Ringkasan')
                                            ->placeholder('Ringkasan singkat yang tampil di kartu artikel, hasil pencarian, dan pratinjau berbagi.')
                                            ->rows(3)
                                            ->maxLength(255)
                                            ->columnSpanFull()
                                            ->helperText('Opsional. Bila kosong, ringkasan otomatis diambil dari awal badan artikel.'),

                                        RichEditor::make('content')
                                            ->label('Badan Artikel')
                                            ->required()
                                            ->columnSpanFull()
                                            ->maxHeight('60vh')
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
                                            ->searchable()
                                            ->afterStateHydrated(function (Select $component, ?Post $record): void {
                                                if ($record === null || ! $record->exists) {
                                                    return;
                                                }

                                                $revision = static::shouldStageRevision($record)
                                                    ? app(PostRevisionService::class)->workingRevision($record)
                                                    : null;

                                                $component->state($revision?->categories ?? $record->categories()->pluck('post_categories.id')->all());
                                            })
                                            ->saveRelationshipsUsing(function (?Post $record, array $state): void {
                                                static::stageOrSyncRelation($record, 'categories', $state);
                                            }),

                                        TagsInput::make('tags')
                                            ->label('Tag')
                                            ->placeholder('Ketik tag lalu tekan Enter, pisahkan dengan koma')
                                            ->suggestions(fn () => PostTag::pluck('name')->toArray())
                                            ->splitKeys([','])
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function (TagsInput $component, ?Post $record): void {
                                                if ($record === null || ! $record->exists) {
                                                    return;
                                                }
                                                $revision = static::shouldStageRevision($record)
                                                    ? app(PostRevisionService::class)->workingRevision($record)
                                                    : null;

                                                $component->state($revision?->tags ?? $record->tags()->pluck('name')->toArray());
                                            })
                                            ->saveRelationshipsUsing(function (?Post $record, array $state): void {
                                                static::stageOrSyncRelation($record, 'tags', $state);
                                            }),
                                    ]),

                                Section::make('Penerbitan')
                                    ->schema([
                                        Toggle::make('allow_comments')
                                            ->label('Izinkan Komentar')
                                            ->default(true),

                                        Placeholder::make('review_note')
                                            ->label('Catatan Review Sebelumnya')
                                            ->content(fn (?Post $record): string => static::revisionRejectionReason($record) ?? $record?->rejection_reason ?? '-')
                                            ->visible(fn (?Post $record): bool => filled(static::revisionRejectionReason($record)) || filled($record?->rejection_reason)),

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
