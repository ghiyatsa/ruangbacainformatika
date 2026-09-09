<?php

use App\Models\DocumentSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

beforeEach(function (): void {
    Storage::fake('documents');
});

it('rejects guests from downloading document files', function () {
    $submission = DocumentSubmission::factory()->create();

    get(route('documents.file', [$submission, 'document']))
        ->assertRedirect(route('login'));
});

it('allows the submission owner to download their document file', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'internship-reports/submissions/owned.pdf',
    ]);

    Storage::disk('documents')->put(
        'internship-reports/submissions/owned.pdf',
        'PDF CONTENT',
    );

    $this->actingAs($owner)
        ->get(route('documents.file', [$submission, 'document']))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Cache-Control', 'private, no-store');
});

it('allows administrative users to download any document file', function (string $role) {
    $owner = User::factory()->create();
    $staff = User::factory()->{$role}()->create();

    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'internship-reports/submissions/staff.pdf',
    ]);

    Storage::disk('documents')->put(
        'internship-reports/submissions/staff.pdf',
        'PDF CONTENT',
    );

    $this->actingAs($staff)
        ->get(route('documents.file', [$submission, 'document']))
        ->assertOk();
})->with(['staff', 'admin']);

it('forbids other members from downloading a document file they do not own', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'internship-reports/submissions/secret.pdf',
    ]);

    Storage::disk('documents')->put(
        'internship-reports/submissions/secret.pdf',
        'PDF CONTENT',
    );

    $this->actingAs($stranger)
        ->get(route('documents.file', [$submission, 'document']))
        ->assertForbidden();
});

it('returns not found when the requested field is empty', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'endorsement_file_path' => null,
    ]);

    $this->actingAs($owner)
        ->get(route('documents.file', [$submission, 'endorsement']))
        ->assertNotFound();
});

it('returns not found when the file is missing from storage', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'internship-reports/submissions/gone.pdf',
    ]);

    $this->actingAs($owner)
        ->get(route('documents.file', [$submission, 'document']))
        ->assertNotFound();
});

it('serves the endorsement file when requested', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'endorsement_file_path' => 'internship-reports/endorsements/endorsement.pdf',
    ]);

    Storage::disk('documents')->put(
        'internship-reports/endorsements/endorsement.pdf',
        'ENDORSEMENT CONTENT',
    );

    $this->actingAs($owner)
        ->get(route('documents.file', [$submission, 'endorsement']))
        ->assertOk();
});

it('rejects an unknown file field', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->get("/documents/{$submission->id}/password")
        ->assertNotFound();
});
