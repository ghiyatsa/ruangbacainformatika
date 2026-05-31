<?php

namespace App\Filament\Resources\StaticPages\Pages;

use App\Filament\Resources\StaticPages\StaticPageResource;
use App\Models\StaticPage;
use App\Services\ActivityLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateStaticPage extends CreateRecord
{
    protected static string $resource = StaticPageResource::class;

    protected function afterCreate(): void
    {
        /** @var StaticPage $record */
        $record = $this->record;

        app(ActivityLogService::class)->log(
            'static_pages.created',
            'Halaman statis dibuat',
            $record,
            [
                'slug' => $record->slug,
                'page_key' => $record->page_key,
                'is_active' => $record->is_active,
            ],
        );
    }
}
