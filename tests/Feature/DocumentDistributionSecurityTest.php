<?php

use App\Models\DocumentSubmission;
use App\Models\User;
use App\Repositories\SettingRepository;
use App\Services\DocumentDistributionService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('documents');
});

test('backend blocks submission when period is closed', function () {
    $settings = app(SettingRepository::class);
    $settings->put('library', 'distribution_kp_active', '0');

    $user = User::factory()->create([
        'email' => '210170099@mhs.unimal.ac.id',
    ]);

    $service = app(DocumentDistributionService::class);

    expect(fn () => $service->submit(
        $user,
        DocumentSubmission::TYPE_INTERNSHIP_REPORT,
        [
            'title' => 'Coba Submit Saat Tutup',
            'company_name' => 'PT Test',
            'academic_advisor' => 'Dosen Test',
            'year' => 2026,
            'abstract' => 'Abstrak pengujian keamanan backend.',
        ]
    ))->toThrow(HttpException::class);
});

test('backend blocks modification of approved submission', function () {
    $user = User::factory()->create([
        'email' => '210170088@mhs.unimal.ac.id',
    ]);

    $submission = DocumentSubmission::factory()->approved()->create([
        'user_id' => $user->id,
        'type' => DocumentSubmission::TYPE_SKRIPSI,
    ]);

    $service = app(DocumentDistributionService::class);

    expect(fn () => $service->submit(
        $user,
        DocumentSubmission::TYPE_SKRIPSI,
        [
            'title' => 'Judul Baru Setelah Disetujui',
            'academic_advisor' => 'Dosen Baru',
            'year' => 2026,
            'abstract' => 'Abstrak pengujian revisi data disetujui.',
        ]
    ))->toThrow(HttpException::class);
});

test('unauthenticated user cannot access distribution receipt', function () {
    $submission = DocumentSubmission::factory()->approved()->create();

    $this->get(route('distribution.receipt', ['token' => $submission->receipt_token]))
        ->assertRedirect(route('login'));
});

test('member cannot access another members distribution receipt', function () {
    $owner = User::factory()->create(['email' => '210170010@mhs.unimal.ac.id']);
    $otherMember = User::factory()->create(['email' => '210170011@mhs.unimal.ac.id']);

    $submission = DocumentSubmission::factory()->approved()->create([
        'user_id' => $owner->id,
    ]);

    $this->actingAs($otherMember)
        ->get(route('distribution.receipt', ['token' => $submission->receipt_token]))
        ->assertForbidden();
});

test('owner and admin can access distribution receipt', function () {
    $owner = User::factory()->create(['email' => '210170012@mhs.unimal.ac.id']);
    $admin = User::factory()->admin()->create();

    $submission = DocumentSubmission::factory()->approved()->create([
        'user_id' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->get(route('distribution.receipt', ['token' => $submission->receipt_token]))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('distribution.receipt', ['token' => $submission->receipt_token]))
        ->assertOk();
});
