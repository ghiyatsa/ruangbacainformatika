<?php

use App\Filament\Resources\DocumentSubmissions\Pages\ListDocumentSubmissions;
use App\Filament\Resources\DocumentSubmissions\Pages\ViewDocumentSubmission;
use App\Models\DocumentSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
});

it('can render list document submissions page for staff', function () {
    $staff = User::factory()->admin()->create();
    actingAs($staff);

    DocumentSubmission::factory()->count(3)->create();

    Livewire::test(ListDocumentSubmissions::class)
        ->assertSuccessful();
});

it('can render view document submission page', function () {
    $staff = User::factory()->admin()->create();
    actingAs($staff);

    $submission = DocumentSubmission::factory()->create();

    Livewire::test(ViewDocumentSubmission::class, ['record' => $submission->getRouteKey()])
        ->assertSuccessful()
        ->assertSee($submission->title);
});

it('can approve submission and publish record', function () {
    $staff = User::factory()->admin()->create();
    actingAs($staff);

    $submission = DocumentSubmission::factory()->create([
        'status' => DocumentSubmission::STATUS_PENDING,
        'document_file_path' => 'submissions/internship_report/sample.pdf',
    ]);

    Storage::disk('public')->put('submissions/internship_report/sample.pdf', 'dummy content');

    Livewire::test(ViewDocumentSubmission::class, ['record' => $submission->getRouteKey()])
        ->callAction('approve')
        ->assertNotified();

    expect($submission->fresh()->status)->toBe(DocumentSubmission::STATUS_APPROVED)
        ->and($submission->fresh()->receipt_number)->not->toBeNull();
});
