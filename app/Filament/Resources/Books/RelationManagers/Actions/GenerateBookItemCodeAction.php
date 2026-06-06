<?php

namespace App\Filament\Resources\Books\RelationManagers\Actions;

use App\Support\BookItemCodeGenerator;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class GenerateBookItemCodeAction
{
    public static function make(): Action
    {
        return Action::make('generateBarcode')
            ->icon(Heroicon::Sparkles)
            ->tooltip('Isi kode otomatis')
            ->action(function (Set $set, $livewire): void {
                $book = $livewire->getOwnerRecord();
                $nextNumber = $book->items()->count() + 1;

                $set('internal_code', BookItemCodeGenerator::generate($book, $nextNumber));
            });
    }
}
