<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\Posts\Pages;

use App\Filament\Dashboard\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\PostRevision;
use App\Services\Post\PostRevisionService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
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

    protected function revisionService(): PostRevisionService
    {
        return app(PostRevisionService::class);
    }

    protected function workingRevision(): ?PostRevision
    {
        return $this->revisionService()->workingRevision($this->record);
    }

    /**
     * Artikel yang sudah terbit tidak ditampilkan sebagai draf baru: yang
     * ditampilkan adalah perubahan yang sedang menunggu tinjauan.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $revision = $this->workingRevision();

        if ($revision !== null) {
            $data['title'] = $revision->title;
            $data['slug'] = $revision->slug;
            $data['summary'] = $revision->summary;
            $data['content'] = $revision->content;
            $data['cover_image'] = $revision->cover_image;
            $data['allow_comments'] = $revision->allow_comments;
            $data['categories'] = $revision->categories ?? [];
            $data['status'] = $revision->status === PostRevision::STATUS_PENDING
                ? Post::STATUS_PENDING
                : Post::STATUS_DRAFT;

            return $data;
        }

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
                    $this->js("window.open('".route('posts.preview', $this->record->preview_token)."', '_blank')");
                }),
            DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = $this->record->user_id;
        $data['status'] = $data['status'] === Post::STATUS_PENDING
            ? Post::STATUS_PENDING
            : Post::STATUS_DRAFT;
        $data['reviewed_by_user_id'] = null;
        $data['reviewed_at'] = null;
        $data['rejection_reason'] = null;

        return $data;
    }

    /**
     * Artikel yang sudah terbit hanya menerima perubahan lewat revisi,
     * sehingga versi yang sedang tayang tidak ikut berubah.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Post $record */
        if ($record->status === Post::STATUS_APPROVED) {
            $this->revisionService()->stageContent($record, $data);

            return $record;
        }

        $record->update($data);

        return $record;
    }

    protected function getSaveFormAction(): Action
    {
        $isApproved = $this->record->status === Post::STATUS_APPROVED;
        $submitting = ($this->data['status'] ?? null) === Post::STATUS_PENDING;

        $label = match (true) {
            $submitting && $isApproved => 'Ajukan Perubahan',
            $submitting => 'Ajukan Artikel',
            $isApproved => 'Simpan Draf Perubahan',
            default => 'Simpan Draf',
        };

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
