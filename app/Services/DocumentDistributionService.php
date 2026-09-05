<?php

namespace App\Services;

use App\Models\Book;
use App\Models\DocumentSubmission;
use App\Models\InternshipReport;
use App\Models\Publisher;
use App\Models\Skripsi;
use App\Models\User;
use App\Repositories\SettingRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentDistributionService
{
    public function isPeriodOpen(?string $type = null): bool
    {
        $settings = app(SettingRepository::class);

        $typeKey = match ($type) {
            DocumentSubmission::TYPE_INTERNSHIP_REPORT => 'kp',
            DocumentSubmission::TYPE_SKRIPSI => 'skripsi',
            DocumentSubmission::TYPE_BOOK_DONATION => 'book',
            default => null,
        };

        if ($typeKey) {
            $isActive = filter_var($settings->get('library', "distribution_{$typeKey}_active", true), FILTER_VALIDATE_BOOLEAN);
            if (! $isActive) {
                return false;
            }

            $now = now();
            $start = $settings->get('library', "distribution_{$typeKey}_start");
            $end = $settings->get('library', "distribution_{$typeKey}_end");

            if ($start && $now->lt(Carbon::parse($start)->startOfDay())) {
                return false;
            }

            if ($end && $now->gt(Carbon::parse($end)->endOfDay())) {
                return false;
            }

            return true;
        }

        // Jika tipe tidak ditentukan, cek apakah setidaknya salah satu periode buka
        return $this->isPeriodOpen(DocumentSubmission::TYPE_INTERNSHIP_REPORT)
            || $this->isPeriodOpen(DocumentSubmission::TYPE_SKRIPSI)
            || $this->isPeriodOpen(DocumentSubmission::TYPE_BOOK_DONATION);
    }

    public function periodClosedMessage(): string
    {
        return app(SettingRepository::class)->get(
            'library',
            'distribution_closed_message',
            'Periode penyerahan berkas bebas pustaka saat ini belum dibuka / telah ditutup oleh pengelola Ruang Baca.'
        );
    }

    /**
     * Submit atau update pengajuan dokumen (KP, Skripsi, atau Sumbangan Buku) dari mahasiswa.
     *
     * @param  array<string, mixed>  $data
     */
    public function submit(User $user, string $type, array $data, ?UploadedFile $documentFile = null, ?UploadedFile $endorsementFile = null): DocumentSubmission
    {
        $submission = DocumentSubmission::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        // 1. Backend Security: Tolak jika dokumen sudah berstatus disetujui (Approved)
        if ($submission && $submission->isApproved()) {
            abort(403, 'Pengajuan ini telah disetujui secara resmi dan tidak dapat diubah lagi.');
        }

        // 2. Backend Security: Tolak jika periode pendaftaran ditutup (kecuali status sedang diminta revisi)
        $isRevision = $submission && $submission->isRevision();
        if (! $this->isPeriodOpen($type) && ! $isRevision) {
            abort(403, $this->periodClosedMessage());
        }

        return DB::transaction(function () use ($user, $type, $data, $documentFile, $endorsementFile, $submission) {
            $docPath = $submission?->document_file_path;
            if ($documentFile) {
                if ($docPath && Storage::disk('documents')->exists($docPath)) {
                    Storage::disk('documents')->delete($docPath);
                }
                $docPath = $documentFile->store("submissions/{$type}", 'documents');
            }

            $endorsementPath = $submission?->endorsement_file_path;
            if ($endorsementFile) {
                if ($endorsementPath && Storage::disk('documents')->exists($endorsementPath)) {
                    Storage::disk('documents')->delete($endorsementPath);
                }
                $endorsementPath = $endorsementFile->store('submissions/endorsements', 'documents');
            }

            $payload = [
                'user_id' => $user->id,
                'type' => $type,
                'title' => $data['title'],
                'year' => isset($data['year']) ? (int) $data['year'] : (int) date('Y'),
                'abstract' => $data['abstract'] ?? null,
                'keywords' => is_array($data['keywords'] ?? null) ? implode(', ', $data['keywords']) : ($data['keywords'] ?? null),
                'academic_advisor' => $data['academic_advisor'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'company_address' => $data['company_address'] ?? null,
                'field_advisor' => $data['field_advisor'] ?? null,
                'author_names' => $data['author_names'] ?? null,
                'publisher_name' => $data['publisher_name'] ?? null,
                'isbn' => $data['isbn'] ?? null,
                'edition' => $data['edition'] ?? null,
                'pages' => $data['pages'] ?? null,
                'book_condition' => $data['book_condition'] ?? 'good',
                'document_file_path' => $docPath,
                'endorsement_file_path' => $endorsementPath,
                'status' => DocumentSubmission::STATUS_PENDING,
                'revision_notes' => null,
            ];

            if ($submission) {
                $submission->update($payload);

                return $submission->fresh();
            }

            return DocumentSubmission::query()->create($payload);
        });
    }

    /**
     * Setujui pengajuan dokumen, terbitkan ke katalog resmi terkait, dan buat nomor tanda terima.
     */
    public function approve(DocumentSubmission $submission, User $reviewer): DocumentSubmission
    {
        return DB::transaction(function () use ($submission, $reviewer) {
            $studentId = $submission->user->identityNumber() ?? $submission->user->email;
            $submittable = null;

            // 1. Publikasi Laporan KP
            if ($submission->type === DocumentSubmission::TYPE_INTERNSHIP_REPORT) {
                $publishedFilePath = null;
                if ($submission->document_file_path && Storage::disk('documents')->exists($submission->document_file_path)) {
                    $ext = pathinfo($submission->document_file_path, PATHINFO_EXTENSION);
                    $newPath = 'internship-reports/published/'.Str::slug($submission->user->name ?? 'report').'-'.$submission->user_id.'-'.time().'.'.$ext;
                    // Salin ke path published — tetap di disk 'documents' (private), dilayani via controller
                    Storage::disk('documents')->put(
                        $newPath,
                        Storage::disk('documents')->get($submission->document_file_path)
                    );
                    $publishedFilePath = $newPath;
                }

                $submittable = InternshipReport::query()->updateOrCreate(
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
            }

            // 2. Publikasi Skripsi
            elseif ($submission->type === DocumentSubmission::TYPE_SKRIPSI) {
                $publishedFilePath = null;
                if ($submission->document_file_path && Storage::disk('documents')->exists($submission->document_file_path)) {
                    $ext = pathinfo($submission->document_file_path, PATHINFO_EXTENSION);
                    $newPath = 'skripsis/published/'.Str::slug($submission->user->name ?? 'skripsi').'-'.$submission->user_id.'-'.time().'.'.$ext;
                    // Salin ke path published — tetap di disk 'documents' (private), dilayani via controller
                    Storage::disk('documents')->put(
                        $newPath,
                        Storage::disk('documents')->get($submission->document_file_path)
                    );
                    $publishedFilePath = $newPath;
                }

                $submittable = Skripsi::query()->updateOrCreate(
                    ['student_id' => $studentId],
                    [
                        'title' => $submission->title,
                        'author_name' => $submission->user->name,
                        'student_id' => $studentId,
                        'year' => $submission->year,
                        'abstract' => $submission->abstract,
                        'keywords' => $submission->keywords,
                        'file_path' => $publishedFilePath,
                        'academic_advisor' => $submission->academic_advisor,
                    ]
                );
            }

            // 3. Sumbangan Buku
            elseif ($submission->type === DocumentSubmission::TYPE_BOOK_DONATION) {
                if ($submission->submittable_type === Book::class && $submission->submittable_id) {
                    $book = Book::query()->find($submission->submittable_id);
                }

                if (! isset($book) || ! $book) {
                    $publisherId = $submission->publisher_id;
                    if (! $publisherId && filled($submission->publisher_name)) {
                        $publisher = Publisher::query()->firstOrCreate(
                            ['name' => trim($submission->publisher_name)],
                            ['slug' => Publisher::generateUniqueSlug(trim($submission->publisher_name))]
                        );
                        $publisherId = $publisher->id;
                    }

                    $book = Book::query()->create([
                        'title' => $submission->title,
                        'subtitle' => $submission->subtitle,
                        'slug' => $submission->slug ?: Book::generateSlugPreview($submission->title),
                        'description' => $submission->description,
                        'isbn' => $submission->isbn,
                        'issn' => $submission->issn,
                        'ddc_code' => $submission->ddc_code,
                        'language' => $submission->language ?: 'Indonesia',
                        'edition' => $submission->edition,
                        'pages' => $submission->pages,
                        'published_year' => $submission->year,
                        'publisher_id' => $publisherId,
                        'cover_image' => $submission->cover_image,
                        'is_published' => true,
                        'is_borrowable' => true,
                    ]);

                    if (! empty($submission->author_ids)) {
                        $book->authors()->sync($submission->author_ids);
                    }

                    if (! empty($submission->category_ids)) {
                        $book->categories()->sync($submission->category_ids);
                    }
                }

                // Tambahkan eksemplar awal / tambahan stok
                $copies = max(1, (int) ($submission->copies_count ?? 1));
                app(BookItemBatchCreator::class)->create(
                    $book,
                    $copies,
                );

                $submittable = $book;
            }

            // Generate nomor tanda terima
            $receiptNumber = $submission->receipt_number;
            if (empty($receiptNumber)) {
                $prefix = match ($submission->type) {
                    DocumentSubmission::TYPE_INTERNSHIP_REPORT => 'KP',
                    DocumentSubmission::TYPE_SKRIPSI => 'SKR',
                    DocumentSubmission::TYPE_BOOK_DONATION => 'BK',
                    default => 'DOC',
                };

                $count = DocumentSubmission::query()
                    ->whereYear('created_at', now()->year)
                    ->where('type', $submission->type)
                    ->whereNotNull('receipt_number')
                    ->count() + 1;

                $receiptNumber = sprintf('%s/RB-IF/%s/%03d', $prefix, now()->format('Y/m'), $count);
            }

            $submission->update([
                'status' => DocumentSubmission::STATUS_APPROVED,
                'submittable_type' => $submittable ? $submittable::class : null,
                'submittable_id' => $submittable?->getKey(),
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'receipt_number' => $receiptNumber,
                'revision_notes' => null,
            ]);

            return $submission->fresh(['user', 'submittable']);
        });
    }

    /**
     * Minta revisi pengajuan dokumen.
     */
    public function requestRevision(DocumentSubmission $submission, User $reviewer, string $notes): DocumentSubmission
    {
        $submission->update([
            'status' => DocumentSubmission::STATUS_REVISION,
            'revision_notes' => $notes,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $submission->fresh();
    }
}
