<?php

namespace App\Filament\Resources\WhatsAppMessageLogs\Pages;

use App\Filament\Resources\WhatsAppMessageLogs\WhatsAppMessageLogsResource;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppMessageLogs extends ListRecords
{
    protected static string $resource = WhatsAppMessageLogsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
