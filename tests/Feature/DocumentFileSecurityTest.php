<?php

use App\Models\DocumentSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Storage::fake('documents');

    Storage::disk('documents')->put('submissions/skripsi/test-doc.pdf', 'fake-pdf-content');
    Storage::disk('documents')->put('submissions/endorsements/test-endorsement.pdf', 'fake-endorsement-content');
});

it('blocks unauthenticated access to document files', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'submissions/skripsi/test-doc.pdf',
        'endorsement_file_path' => 'submissions/endorsements/test-endorsement.pdf',
    ]);

    get(route('documents.file', ['submission' => $submission->id, 'field' => 'document']))
        ->assertRedirectContains('/login');
});

it('allows owner to access their own document', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'submissions/skripsi/test-doc.pdf',
    ]);

    actingAs($owner)
        ->get(route('documents.file', ['submission' => $submission->id, 'field' => 'document']))
        ->assertOk()
        ->assertHeaderMissing('X-Robots-Tag'); // file berhasil distream, bukan redirect
});

it('allows staff/admin to access any document', function () {
    Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

    $owner = User::factory()->create();
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'submissions/skripsi/test-doc.pdf',
    ]);

    actingAs($staff)
        ->get(route('documents.file', ['submission' => $submission->id, 'field' => 'document']))
        ->assertOk();
});

it('blocks other authenticated users from accessing documents they do not own', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'submissions/skripsi/test-doc.pdf',
    ]);

    actingAs($otherUser)
        ->get(route('documents.file', ['submission' => $submission->id, 'field' => 'document']))
        ->assertForbidden();
});

it('returns 404 for invalid field parameter', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'submissions/skripsi/test-doc.pdf',
    ]);

    // Route constraint where('field', 'document|endorsement') harus reject field lain
    actingAs($owner)
        ->get("/documents/{$submission->id}/invalid-field")
        ->assertNotFound();
});

it('returns 404 when document file path is empty', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => null,
    ]);

    actingAs($owner)
        ->get(route('documents.file', ['submission' => $submission->id, 'field' => 'document']))
        ->assertNotFound();
});

it('returns 404 when file does not exist on disk', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'submissions/skripsi/nonexistent.pdf',
    ]);

    actingAs($owner)
        ->get(route('documents.file', ['submission' => $submission->id, 'field' => 'document']))
        ->assertNotFound();
});

it('sends security headers to prevent content sniffing', function () {
    $owner = User::factory()->create();
    $submission = DocumentSubmission::factory()->create([
        'user_id' => $owner->id,
        'document_file_path' => 'submissions/skripsi/test-doc.pdf',
    ]);

    $response = actingAs($owner)
        ->get(route('documents.file', ['submission' => $submission->id, 'field' => 'document']));

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    // Cache-Control bisa "private, no-store" atau "no-store, private" tergantung implementasi HTTP
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
    expect($response->headers->get('Cache-Control'))->toContain('private');
});
