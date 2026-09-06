<?php

namespace App\Filament\Resources\DocumentSubmissions\Pages;

use App\Filament\Resources\DocumentSubmissions\DocumentSubmissionResource;
use App\Models\DocumentSubmission;
use App\Models\User;
use App\Services\DocumentDistributionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewDocumentSubmission extends ViewRecord
{
    protected static string $resource = DocumentSubmissionResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadDocument')
                ->label('Unduh Dokumen PDF')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('info')
                ->visible(fn (): bool => ! empty($this->record->document_file_path))
                ->url(fn (): string => route('documents.file', ['submission' => $this->record->id, 'field' => 'document']))
                ->openUrlInNewTab(),

            Action::make('viewEndorsement')
                ->label('Lihat Pengesahan')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->visible(fn (): bool => $this->record->type !== DocumentSubmission::TYPE_BOOK_DONATION && ! empty($this->record->endorsement_file_path))
                ->url(fn (): string => route('documents.file', ['submission' => $this->record->id, 'field' => 'endorsement']))
                ->openUrlInNewTab(),

            Action::make('approve')
                ->label('Setujui Pengajuan')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Persetujuan Dokumen')
                ->modalDescription('Dokumen akan diverifikasi, otomatis terbit ke katalog resmi, dan nomor tanda terima diterbitkan.')
                ->visible(fn (): bool => $this->record->status !== DocumentSubmission::STATUS_APPROVED)
                ->action(function (): void {
                    /** @var User $user */
                    $user = auth()->user();
                    app(DocumentDistributionService::class)->approve($this->record, $user);

                    Notification::make()
                        ->success()
                        ->title('Pengajuan berhasil disetujui')
                        ->body('Dokumen resmi diterbitkan ke katalog perpustakaan.')
                        ->send();

                    $this->refreshFormData(['status', 'receipt_number', 'reviewed_by', 'reviewed_at', 'submittable_type', 'submittable_id']);
                }),

            Action::make('requestRevision')
                ->label('Minta Revisi')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->color('warning')
                ->visible(fn (): bool => $this->record->status !== DocumentSubmission::STATUS_APPROVED)
                ->schema([
                    Textarea::make('revision_notes')
                        ->label('Catatan Revisi')
                        ->placeholder('Tuliskan poin perbaikan berkas secara spesifik...')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    /** @var User $user */
                    $user = auth()->user();
                    app(DocumentDistributionService::class)->requestRevision($this->record, $user, $data['revision_notes']);

                    Notification::make()
                        ->warning()
                        ->title('Permintaan revisi terkirim')
                        ->body('Status pengajuan diperbarui menjadi Perlu Revisi.')
                        ->send();

                    $this->refreshFormData(['status', 'revision_notes', 'reviewed_by', 'reviewed_at']);
                }),
        ];
    }
}
