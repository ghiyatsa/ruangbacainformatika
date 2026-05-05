<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use Filament\Resources\Pages\EditRecord;

class EditAuthor extends EditRecord
{
    protected static string $resource = AuthorResource::class;
}
