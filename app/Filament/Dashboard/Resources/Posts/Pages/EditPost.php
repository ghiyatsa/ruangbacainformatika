<?php

namespace App\Filament\Dashboard\Resources\Posts\Pages;

use App\Filament\Dashboard\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
            Action::make('preview')
                ->label('Pratinjau')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->action(function () {
                    Cache::put(
                        'post_preview_'.$this->record->preview_token,
                        $this->sanitizePreviewData($this->data),
                        now()->addMinutes(10)
                    );
                    $this->js("window.open('".route('blog.preview', $this->record->preview_token)."', '_blank')");
                }),
            DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function sanitizePreviewData(array $data): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->sanitizePreviewData($value);
            }
            if ($value instanceof TemporaryUploadedFile) {
                try {
                    $previewPath = 'posts/previews/'.$value->getFilename();
                    Storage::disk('public')->put(
                        $previewPath,
                        $value->get()
                    );

                    return $previewPath;
                } catch (\Exception $e) {
                    return $value->getFilename();
                }
            }
            if (is_object($value)) {
                return null;
            }

            return $value;
        }, $data);
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

    public function updated($property): void
    {

        if (str_starts_with($property, 'data.')) {
            Cache::put(
                'post_preview_'.$this->record->preview_token,
                $this->sanitizePreviewData($this->data),
                now()->addHours(2)
            );
        }
    }

    protected function afterSave(): void
    {
        Cache::forget('post_preview_'.$this->record->preview_token);
    }
}
