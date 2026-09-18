<?php

declare(strict_types=1);

namespace App\Filament\Resources\Theses\Pages;

use App\Filament\Resources\Theses\ThesisResource;
use Filament\Resources\Pages\CreateRecord;

class CreateThesis extends CreateRecord
{
    protected static string $resource = ThesisResource::class;
}
