<?php

use App\Models\DocumentSubmission;
use App\Models\User;
use App\Services\DocumentDistributionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('documents');
});

test('member can submit internship report and skripsi distribution', function () {
    $user = User::factory()->create([
        'email' => '210170001@mhs.unimal.ac.id',
    ]);

    $service = app(DocumentDistributionService::class);

    $submission = $service->submit(
        $user,
        DocumentSubmission::TYPE_INTERNSHIP_REPORT,
        [
            'title' => 'Laporan Kerja Praktik Test',
            'company_name' => 'PT Test',
            'academic_advisor' => 'Dosen Test',
            'year' => 2026,
            'abstract' => 'Abstrak kerja praktik pengujian sistem terdistribusi.',
        ],
        UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
    );

    expect($submission->status)->toBe(DocumentSubmission::STATUS_PENDING)
        ->and($submission->type)->toBe(DocumentSubmission::TYPE_INTERNSHIP_REPORT)
        ->and($submission->receipt_token)->not->toBeNull();

    // File disimpan di disk 'documents' (private), bukan 'public'
    Storage::disk('documents')->assertExists($submission->document_file_path);
    Storage::disk('public')->assertMissing($submission->document_file_path);
});

test('admin can approve document submission and publish to catalog', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'email' => '210170002@mhs.unimal.ac.id',
    ]);

    $submission = DocumentSubmission::factory()->create([
        'user_id' => $user->id,
        'type' => DocumentSubmission::TYPE_INTERNSHIP_REPORT,
        'status' => DocumentSubmission::STATUS_PENDING,
        'title' => 'Laporan KP Siap Terbit',
        'document_file_path' => 'submissions/internship_report/test.pdf',
    ]);

    // Saat approve, file submission ada di disk 'documents' (private)
    Storage::disk('documents')->put('submissions/internship_report/test.pdf', 'dummy content');

    $service = app(DocumentDistributionService::class);
    $approved = $service->approve($submission, $admin);

    expect($approved->status)->toBe(DocumentSubmission::STATUS_APPROVED)
        ->and($approved->receipt_number)->not->toBeNull()
        ->and($approved->submittable_id)->not->toBeNull();

    // Saat disetujui, file dipublish ke disk 'documents' (private) — dilayani via AcademicFileController
    $publishedFiles = Storage::disk('documents')->allFiles('internship-reports/published');
    expect($publishedFiles)->not->toBeEmpty();
});

test('admin can request revision on submission and sends notification with revision notes', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'email' => '210170003@mhs.unimal.ac.id',
    ]);

    $submission = DocumentSubmission::factory()->create([
        'user_id' => $user->id,
        'type' => DocumentSubmission::TYPE_SKRIPSI,
        'status' => DocumentSubmission::STATUS_PENDING,
        'title' => 'Naskah Skripsi Belum Lengkap',
    ]);

    $service = app(DocumentDistributionService::class);
    $revision = $service->requestRevision($submission, $admin, 'Lembar pengesahan belum ditandatangani');

    expect($revision->status)->toBe(DocumentSubmission::STATUS_REVISION)
        ->and($revision->revision_notes)->toBe('Lembar pengesahan belum ditandatangani')
        ->and($revision->reviewed_by)->toBe($admin->id);

    $notification = $user->notifications()->latest()->first();
    expect($notification)->not->toBeNull()
        ->and($notification->data['revision_notes'])->toBe('Lembar pengesahan belum ditandatangani')
        ->and($notification->data['kind'])->toBe('document_submission_revision');
});
