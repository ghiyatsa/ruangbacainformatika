<?php

namespace App\Filament\Resources\StaticPages\Schemas;

use App\Models\StaticPage;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class StaticPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Toolbar Halaman Statis')
                    ->key('static-page-toolbar-tabs')
                    ->contained(false)
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Informasi Halaman')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Panduan Layanan')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state, ?StaticPage $record): void {
                                        if ($record?->isSystemPage()) {
                                            return;
                                        }

                                        $set('slug', Str::slug($state ?? ''));
                                    }),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('static_pages', 'slug', ignoreRecord: true)
                                    ->readOnly(fn (?StaticPage $record): bool => $record?->isSystemPage() ?? false)
                                    ->helperText('Digunakan pada alamat halaman publik. Untuk halaman bawaan, slug mengikuti rute utama.')
                                    ->placeholder('panduan-layanan'),
                                Toggle::make('is_active')
                                    ->label('Publikasi')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('gray')
                                    ->helperText('Jika dimatikan, halaman kustom tidak tampil di publik.'),
                                Placeholder::make('public_url')
                                    ->label('URL Publik')
                                    ->content(fn (?StaticPage $record): string => $record?->publicUrl() ?? 'URL tersedia setelah halaman disimpan.'),
                            ])
                            ->columns(2),
                        Tab::make('Konten')
                            ->icon(Heroicon::OutlinedPencilSquare)
                            ->schema([
                                Textarea::make('summary')
                                    ->label('Ringkasan halaman')
                                    ->required()
                                    ->rows(3)
                                    ->maxLength(255)
                                    ->placeholder('Ringkasan singkat halaman.'),
                                RichEditor::make('content')
                                    ->label('Isi halaman')
                                    ->required()
                                    ->toolbarButtons([
                                        ['bold', 'italic', 'underline', 'strike', 'link'],
                                        ['h2', 'h3'],
                                        ['blockquote', 'bulletList', 'orderedList'],
                                        ['attachFiles'],
                                        ['undo', 'redo'],
                                    ])
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('static-pages')
                                    ->fileAttachmentsVisibility('public')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
