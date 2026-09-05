<?php

namespace App\Services;

use App\Models\InternshipReport;
use App\Models\InternshipReportSubmission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InternshipDistributionService
{
    /**
     * Submit atau update pengajuan laporan KP mahasiswa.
     *
     * @param  array<string, mixed>  $data
     */
    public function submit(User $user, array $data, ?UploadedFile $reportFile = null, ?UploadedFile $endorsementFile = null): InternshipReportSubmission
    {
        return DB::transaction(function () use ($user, $data, $reportFile, $endorsementFile) {
            $submission = InternshipReportSubmission::query()
                ->where('user_id', $user->id)
                ->first();

            $reportFilePath = $submission?->report_file_path;
            if ($reportFile) {
                if ($reportFilePath && Storage::disk('documents')->exists($reportFilePath)) {
                    Storage::disk('documents')->delete($reportFilePath);
                }
                $reportFilePath = $reportFile->store('internship-reports/submissions', 'documents');
            }

            $endorsementFilePath = $submission?->endorsement_file_path;
            if ($endorsementFile) {
                if ($endorsementFilePath && Storage::disk('documents')->exists($endorsementFilePath)) {
                    Storage::disk('documents')->delete($endorsementFilePath);
                }
                $endorsementFilePath = $endorsementFile->store('internship-reports/endorsements', 'documents');
            }

            $payload = [
                'user_id' => $user->id,
                'title' => $data['title'],
                'company_name' => $data['company_name'],
                'company_address' => $data['company_address'] ?? null,
                'academic_advisor' => $data['academic_advisor'],
                'field_advisor' => $data['field_advisor'] ?? null,
                'year' => (int) $data['year'],
                'abstract' => $data['abstract'],
                'keywords' => is_array($data['keywords'] ?? null) ? implode(', ', $data['keywords']) : ($data['keywords'] ?? null),
                'report_file_path' => $reportFilePath,
                'endorsement_file_path' => $endorsementFilePath,
                'status' => InternshipReportSubmission::STATUS_PENDING,
                'revision_notes' => null,
            ];

            if ($submission) {
                $submission->update($payload);

                return $submission->fresh();
            }

            return InternshipReportSubmission::query()->create($payload);
        });
    }

    /**
     * Setujui pengajuan KP, terbitkan ke katalog resmi, dan generate nomor tanda terima.
     */
    public function approve(InternshipReportSubmission $submission, User $reviewer): InternshipReportSubmission
    {
        return DB::transaction(function () use ($submission, $reviewer) {
            // Salin berkas ke path katalog permanen — tetap di disk 'documents' (private)
            $publishedFilePath = $submission->report_file_path;
            if ($submission->report_file_path && Storage::disk('documents')->exists($submission->report_file_path)) {
                $ext = pathinfo($submission->report_file_path, PATHINFO_EXTENSION);
                $newPath = 'internship-reports/published/'.Str::slug($submission->user->name ?? 'report').'-'.$submission->user_id.'-'.time().'.'.$ext;
                Storage::disk('documents')->put(
                    $newPath,
                    Storage::disk('documents')->get($submission->report_file_path)
                );
                $publishedFilePath = $newPath;
            }

            // Buat atau perbarui record katalog resmi
            $studentId = $submission->user->identityNumber() ?? $submission->user->email;
            $internshipReport = InternshipReport::query()->updateOrCreate(
                ['student_id' => $studentId],
                [
                    'title' => $submission->title,
                    'author_name' => $submission->user->name,
                    'student_id' => $studentId,
                    'year' => $submission->year,
                    'abstract' => $submission->abstract,
                    'keywords' => $submission->keywords,
                    'file_path' => $publishedFilePath,
                    'company_name' => $submission->company_name,
                    'academic_advisor' => $submission->academic_advisor,
                    'field_advisor' => $submission->field_advisor,
                ]
            );

            // Generate nomor receipt jika belum ada
            $receiptNumber = $submission->receipt_number;
            if (empty($receiptNumber)) {
                $count = InternshipReportSubmission::query()
                    ->whereYear('created_at', now()->year)
                    ->whereNotNull('receipt_number')
                    ->count() + 1;
                $receiptNumber = sprintf('KP/RB-IF/%s/%03d', now()->format('Y/m'), $count);
            }

            $submission->update([
                'status' => InternshipReportSubmission::STATUS_APPROVED,
                'internship_report_id' => $internshipReport->id,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'receipt_number' => $receiptNumber,
                'revision_notes' => null,
            ]);

            return $submission->fresh(['user', 'internshipReport']);
        });
    }

    /**
     * Minta revisi pengajuan laporan KP.
     */
    public function requestRevision(InternshipReportSubmission $submission, User $reviewer, string $notes): InternshipReportSubmission
    {
        $submission->update([
            'status' => InternshipReportSubmission::STATUS_REVISION,
            'revision_notes' => $notes,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $submission->fresh();
    }
}
