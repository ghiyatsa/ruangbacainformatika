<?php

namespace App\Filament\Resources\Theses\Pages;

use App\Filament\Resources\Theses\ThesisResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTheses extends ListRecords
{
    protected static string $resource = ThesisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Tesis'),
        ];
    }
}
