<?php

use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Storage::fake('documents');
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

    Storage::disk('documents')->put('internship-reports/published/report-test.pdf', 'dummy-internship-content');
    Storage::disk('documents')->put('skripsis/published/skripsi-test.pdf', 'dummy-skripsi-content');
    Storage::disk('documents')->put('theses/published/thesis-test.pdf', 'dummy-thesis-content');
});

it('blocks unauthenticated guests from downloading published academic PDFs', function () {
    $report = InternshipReport::factory()->create([
        'student_id' => '210170001',
        'file_path' => 'internship-reports/published/report-test.pdf',
    ]);

    get(route('internship-reports.file', $report->student_id))
        ->assertRedirectContains('/login');

    $skripsi = Skripsi::factory()->create([
        'student_id' => '210170002',
        'file_path' => 'skripsis/published/skripsi-test.pdf',
    ]);

    get(route('skripsi.file', $skripsi->student_id))
        ->assertRedirectContains('/login');
});

it('blocks non-member authenticated users from downloading published academic PDFs', function () {
    // User dengan email publik (bukan kampus) tidak akan mendapat role member
    $user = User::factory()->create([
        'email' => 'regular.user@gmail.com',
        'is_approved' => false,
    ]);

    $report = InternshipReport::factory()->create([
        'student_id' => '210170001',
        'file_path' => 'internship-reports/published/report-test.pdf',
    ]);

    actingAs($user)
        ->get(route('internship-reports.file', $report->student_id))
        ->assertForbidden();
});

it('allows active members to download published academic PDFs', function () {
    $member = User::factory()->create([
        'whatsapp' => '081234567890',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kampus',
        'profile_completed_at' => now(),
    ]);
    $member->assignRole('member');

    $report = InternshipReport::factory()->create([
        'student_id' => '210170001',
        'file_path' => 'internship-reports/published/report-test.pdf',
    ]);

    actingAs($member)
        ->get(route('internship-reports.file', $report->student_id))
        ->assertOk();
});

it('sends security headers to prevent sniffing on academic PDFs', function () {
    $member = User::factory()->create([
        'whatsapp' => '081234567890',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kampus',
        'profile_completed_at' => now(),
    ]);
    $member->assignRole('member');

    $report = InternshipReport::factory()->create([
        'student_id' => '210170001',
        'file_path' => 'internship-reports/published/report-test.pdf',
    ]);

    $response = actingAs($member)
        ->get(route('internship-reports.file', $report->student_id));

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
    expect($response->headers->get('Cache-Control'))->toContain('private');
});

it('returns 404 when academic document has no file path', function () {
    $member = User::factory()->create([
        'whatsapp' => '081234567890',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kampus',
        'profile_completed_at' => now(),
    ]);
    $member->assignRole('member');

    $report = InternshipReport::factory()->create([
        'student_id' => '210170001',
        'file_path' => null,
    ]);

    actingAs($member)
        ->get(route('internship-reports.file', $report->student_id))
        ->assertNotFound();
});
