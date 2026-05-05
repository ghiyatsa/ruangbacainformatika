<?php

namespace App\Filament\Resources\Books\Pages;

use App\Filament\Resources\Books\BookResource;
use Filament\Resources\Pages\EditRecord;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;
}
