<?php

namespace App\Filament\Resources\Authors\RelationManagers;

use App\Filament\Resources\Books\BookResource;
use App\Models\Book;
use App\Services\BookCoverImageService;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    protected static ?string $title = 'Daftar Buku yang Ditulis';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'items as available_stock' => fn (Builder $query) => $query->where('status', 'available'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->emptyStateHeading('Belum ada buku yang ditulis')
            ->emptyStateDescription('Penulis ini belum terhubung dengan buku apa pun.')
            ->emptyStateIcon(Heroicon::OutlinedBookOpen)
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->alignCenter()
                    ->defaultImageUrl(app(BookCoverImageService::class)->getDefaultCoverUrl())
                    ->extraImgAttributes([
                        'class' => 'object-contain bg-white p-1',
                    ])
                    ->disk('public'),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('publisher.name')
                    ->label('Penerbit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('published_year')
                    ->label('Tahun')
                    ->sortable(),

                TextColumn::make('available_stock')
                    ->label('Stok')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match (true) {
                        $state == 0 => 'danger',
                        $state <= 2 => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Hubungkan Buku')
                    ->modalHeading('Hubungkan Buku')
                    ->preloadRecordSelect()
                    ->hidden(fn ($livewire) => $livewire->isReadOnly()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->url(fn (Book $record): string => BookResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->label('Ubah')
                    ->url(fn (Book $record): string => BookResource::getUrl('edit', ['record' => $record]))
                    ->hidden(fn ($livewire) => $livewire->isReadOnly()),
                DetachAction::make()
                    ->label('Lepaskan')
                    ->hidden(fn ($livewire) => $livewire->isReadOnly()),
            ])
            ->bulkActions([
                //
            ]);
    }
}
