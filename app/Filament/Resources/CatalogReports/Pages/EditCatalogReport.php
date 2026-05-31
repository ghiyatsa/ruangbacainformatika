<?php

namespace App\Filament\Resources\CatalogReports\Pages;

use App\Filament\Resources\CatalogReports\CatalogReportResource;
use App\Models\CatalogReport;
use App\Services\ActivityLogService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalogReport extends EditRecord
{
    protected static string $resource = CatalogReportResource::class;

    /**
     * @var array<string, mixed>
     */
    protected array $activityLogBefore = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('Lihat detail'),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->activityLogBefore = [
            'status' => $this->record->status,
            'admin_notes' => $this->record->admin_notes,
            'reviewed_at' => $this->record->reviewed_at?->toAtomString(),
        ];

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

    protected function afterSave(): void
    {
        $changes = app(ActivityLogService::class)->diffValues($this->activityLogBefore, [
            'status' => $this->record->status,
            'admin_notes' => $this->record->admin_notes,
            'reviewed_at' => $this->record->reviewed_at?->toAtomString(),
        ]);

        if ($changes === []) {
            return;
        }

        app(ActivityLogService::class)->log(
            'catalog_reports.updated',
            'Tindak lanjut laporan katalog diperbarui',
            $this->record,
            [
                'catalog_type' => $this->record->catalog_type,
                'catalog_title' => $this->record->catalog_title,
                'changes' => $changes,
            ],
        );
    }
}
