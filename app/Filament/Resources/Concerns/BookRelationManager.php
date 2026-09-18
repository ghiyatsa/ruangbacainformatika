<?php

namespace App\Filament\Resources\Concerns;

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

/**
 * Tabel relasi `books` bersama untuk Author / Category / Publisher.
 */
abstract class BookRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    protected static bool $isLazy = false;

    abstract protected function emptyStateHeading(): string;

    abstract protected function emptyStateDescription(): string;

    abstract protected function contextColumn(): TextColumn;

    /**
     * Publisher tidak menyediakan aksi hubungkan/lepaskan buku.
     */
    protected function allowsAttachingBooks(): bool
    {
        return true;
    }

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
        $allowsAttaching = $this->allowsAttachingBooks();

        $recordActions = [
            ViewAction::make()
                ->label('Lihat')
                ->url(fn (Book $record): string => BookResource::getUrl('view', ['record' => $record])),
            EditAction::make()
                ->label('Ubah')
                ->url(fn (Book $record): string => BookResource::getUrl('edit', ['record' => $record]))
                ->hidden(fn ($livewire) => $livewire->isReadOnly()),
        ];

        if ($allowsAttaching) {
            $recordActions[] = DetachAction::make()
                ->label('Lepaskan')
                ->hidden(fn ($livewire) => $livewire->isReadOnly());
        }

        return $table
            ->recordTitleAttribute('title')
            ->emptyStateHeading($this->emptyStateHeading())
            ->emptyStateDescription($this->emptyStateDescription())
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

                $this->contextColumn(),

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
            ->headerActions(
                $allowsAttaching ? [
                    AttachAction::make()
                        ->label('Hubungkan Buku')
                        ->modalHeading('Hubungkan Buku')
                        ->preloadRecordSelect()
                        ->hidden(fn ($livewire) => $livewire->isReadOnly()),
                ] : [
                    //
                ]
            )
            ->recordActions($recordActions)
            ->bulkActions([
                //
            ]);
    }
}
