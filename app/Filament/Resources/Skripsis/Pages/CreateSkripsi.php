<?php

declare(strict_types=1);

namespace App\Filament\Resources\Skripsis\Pages;

use App\Filament\Resources\Skripsis\SkripsiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSkripsi extends CreateRecord
{
    protected static string $resource = SkripsiResource::class;
}
