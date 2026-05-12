<?php

namespace App\Filament\Resources\Theses\Pages;

use App\Filament\Resources\Theses\ThesisResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewThesis extends ViewRecord
{
    protected static string $resource = ThesisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
