<?php

namespace App\Filament\Resources\Books\RelationManagers\Actions;

use App\Support\Library\BookItemBatchCreator;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

class BatchCreateBookItemsAction
{
    public static function make(): Action
    {
        return Action::make('batch_create')
            ->label('Tambah Sekaligus')
            ->icon(Heroicon::OutlinedRectangleStack)
            ->color(Color::Yellow)
            ->schema([
                TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(20)
                    ->default(5),
            ])
            ->action(function (array $data, $livewire): void {
                $book = $livewire->getOwnerRecord();
                $quantity = (int) $data['quantity'];
                app(BookItemBatchCreator::class)->create($book, $quantity);

                Notification::make()
                    ->success()
                    ->title('Berhasil!')
                    ->body("{$quantity} eksemplar berhasil ditambahkan dengan barcode berurutan.")
                    ->send();
            });
    }
}
