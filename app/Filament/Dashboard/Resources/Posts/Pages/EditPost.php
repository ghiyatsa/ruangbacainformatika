<?php

namespace App\Filament\Dashboard\Resources\Posts\Pages;

use App\Filament\Dashboard\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    public function getTitle(): string
    {
        return 'Ubah Artikel';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['status'] ?? null) === Post::STATUS_APPROVED) {
            $data['status'] = Post::STATUS_PENDING;
        }

        if (($data['status'] ?? null) === Post::STATUS_REJECTED) {
            $data['status'] = Post::STATUS_DRAFT;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $wasApproved = $this->record->status === Post::STATUS_APPROVED;

        $data['user_id'] = $this->record->user_id;
        $data['status'] = $data['status'] === Post::STATUS_PENDING || $wasApproved
            ? Post::STATUS_PENDING
            : Post::STATUS_DRAFT;
        $data['reviewed_by_user_id'] = null;
        $data['reviewed_at'] = null;
        $data['rejection_reason'] = null;

        return $data;
    }

    protected function getSaveFormAction(): Action
    {
        $label = ($this->data['status'] ?? null) === Post::STATUS_PENDING
            ? 'Ajukan Artikel'
            : 'Simpan Draf';

        return parent::getSaveFormAction()
            ->label($label);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
}
