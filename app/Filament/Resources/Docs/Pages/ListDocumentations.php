<?php

namespace App\Filament\Resources\Docs\Pages;

use App\Filament\Resources\Docs\DocumentationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentations extends ListRecords
{
    protected static string $resource = DocumentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Panduan'),
        ];
    }
}
