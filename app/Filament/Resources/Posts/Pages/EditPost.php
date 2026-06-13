<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['status'], [Post::STATUS_APPROVED, Post::STATUS_REJECTED])) {
            $data['reviewed_by_user_id'] = auth()->id();
            $data['reviewed_at'] = now();
            if ($data['status'] === Post::STATUS_APPROVED) {
                $data['rejection_reason'] = null;
            }
        }

        return $data;
    }
}
