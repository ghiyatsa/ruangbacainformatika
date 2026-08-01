<?php

namespace App\Filament\Resources\Books\Pages;

use App\Filament\Resources\Books\BookResource;
use App\Models\BookItem;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListBooks extends ListRecords
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Buku'),
        ];
    }

    public function getTabs(): array
    {
        $shelfLocations = BookItem::query()
            ->whereNotNull('shelf_location')
            ->where('shelf_location', '!=', '')
            ->distinct()
            ->orderBy('shelf_location')
            ->pluck('shelf_location')
            ->all();

        $tabs = [
            'all' => Tab::make('Semua')
                ->icon(Heroicon::OutlinedBookOpen)
                ->badge(fn (): int => BookItem::query()->count()),
            'tanpa-rak' => Tab::make('Tanpa Rak')
                ->icon(Heroicon::OutlinedQuestionMarkCircle)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where(
                    fn (Builder $q) => $q
                        ->whereDoesntHave('items')
                        ->orWhereDoesntHave('items', fn (Builder $sub) => $sub
                            ->whereNotNull('shelf_location')
                            ->where('shelf_location', '!=', '')),
                ))
                ->badge(fn (): int => BookItem::query()
                    ->where(fn (Builder $q) => $q
                        ->whereNull('shelf_location')
                        ->orWhere('shelf_location', ''))
                    ->count()),
        ];

        foreach ($shelfLocations as $location) {
            $key = 'rak-'.str($location)->slug();

            $tabs[$key] = Tab::make($location)
                ->icon(Heroicon::OutlinedArchiveBox)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas(
                    'items',
                    fn (Builder $q) => $q->where('shelf_location', $location),
                ))
                ->badge(fn () => BookItem::query()
                    ->where('shelf_location', $location)
                    ->count());
        }

        return $tabs;
    }
}
