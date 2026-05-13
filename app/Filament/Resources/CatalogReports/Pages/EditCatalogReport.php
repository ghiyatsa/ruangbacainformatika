<?php

namespace App\Filament\Resources\CatalogReports\Pages;

use App\Filament\Resources\CatalogReports\CatalogReportResource;
use App\Models\CatalogReport;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalogReport extends EditRecord
{
    protected static string $resource = CatalogReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('Lihat detail'),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentStatus = $this->record->status;
        $nextStatus = $data['status'] ?? $currentStatus;

        if ($currentStatus === CatalogReport::STATUS_PENDING && $nextStatus !== CatalogReport::STATUS_PENDING) {
            $data['reviewed_at'] = now();
        }

        if ($nextStatus === CatalogReport::STATUS_PENDING) {
            $data['reviewed_at'] = null;
        }

        return $data;
    }
}
