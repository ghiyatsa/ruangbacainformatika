<?php

declare(strict_types=1);

namespace App\Filament\Resources\Publishers\Pages;

use App\Filament\Resources\Publishers\PublisherResource;
use Filament\Resources\Pages\EditRecord;

class EditPublisher extends EditRecord
{
    protected static string $resource = PublisherResource::class;
}
