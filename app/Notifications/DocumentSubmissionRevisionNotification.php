<?php

namespace App\Notifications;

use App\Models\DocumentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentSubmissionRevisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected DocumentSubmission $submission,
        protected string $notes,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'document_submission_revision',
            'title' => 'Revisi Pengajuan: '.$this->submission->typeLabel(),
            'message' => 'Pengajuan "'.$this->submission->title.'" memerlukan perbaikan. Catatan: '.$this->notes,
            'action_label' => 'Buka Pengajuan',
            'action_url' => '/dashboard/document-distribution-page',
            'icon' => 'exclamation-triangle',
            'submission_id' => $this->submission->id,
            'submission_type' => $this->submission->type,
            'revision_notes' => $this->notes,
        ];
    }
}
