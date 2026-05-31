<?php

namespace App\Filament\Resources\StaticPages\Pages;

use App\Filament\Resources\StaticPages\StaticPageResource;
use App\Models\StaticPage;
use App\Services\ActivityLogService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStaticPage extends EditRecord
{
    protected static string $resource = StaticPageResource::class;

    /**
     * @var array<string, mixed>
     */
    protected array $activityLogBefore = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus')
                ->visible(fn (): bool => ! ($this->record instanceof StaticPage && $this->record->isSystemPage())),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->activityLogBefore = [
            'title' => $this->record->title,
            'slug' => $this->record->slug,
            'summary' => $this->record->summary,
            'content' => $this->record->content,
            'is_active' => $this->record->is_active,
        ];

        if ($this->record instanceof StaticPage && $this->record->isSystemPage()) {
            $data['slug'] = $this->record->slug;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $changes = app(ActivityLogService::class)->diffValues($this->activityLogBefore, [
            'title' => $this->record->title,
            'slug' => $this->record->slug,
            'summary' => $this->record->summary,
            'content' => $this->record->content,
            'is_active' => $this->record->is_active,
        ]);

        if ($changes === []) {
            return;
        }

        app(ActivityLogService::class)->log(
            'static_pages.updated',
            'Halaman statis diperbarui',
            $this->record,
            [
                'page_key' => $this->record->page_key,
                'changes' => $changes,
            ],
        );
    }
}
