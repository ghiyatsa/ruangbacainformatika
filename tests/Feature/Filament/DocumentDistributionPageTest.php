<?php

use App\Filament\Dashboard\Pages\DocumentDistributionPage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
});

it('it allows mahasiswa member to access document distribution page', function () {
    $mahasiswa = User::factory()->create([
        'email' => '210170014@mhs.unimal.ac.id',
        'is_approved' => true,
    ]);

    actingAs($mahasiswa);

    expect(DocumentDistributionPage::canAccess())->toBeTrue();

    Livewire::test(DocumentDistributionPage::class)
        ->assertSuccessful();
});

it('it restricts non-mahasiswa members from accessing distribution page', function () {
    $dosenMember = User::factory()->create([
        'email' => 'dosen@unimal.ac.id',
        'is_approved' => true,
    ]);

    actingAs($dosenMember);

    expect(DocumentDistributionPage::canAccess())->toBeFalse();
});
