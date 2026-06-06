<?php

namespace App\Filament\Resources\Books\RelationManagers\Actions;

use App\Services\BookItemBatchCreator;
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
            ->label('Tambah Beberapa Eksemplar')
            ->icon(Heroicon::OutlinedRectangleStack)
            ->color(Color::Yellow)
            ->modalHeading('Tambah Beberapa Eksemplar')
            ->modalDescription('Eksemplar baru akan ditambahkan secara berurutan.')
            ->modalSubmitActionLabel('Simpan')
            ->schema([
                TextInput::make('quantity')
                    ->label('Jumlah Eksemplar')
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
                    ->title('Eksemplar ditambahkan')
                    ->body("{$quantity} eksemplar baru ditambahkan.")
                    ->send();
            });
    }
}
