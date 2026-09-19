<?php

use App\Filament\Dashboard\Pages\DocumentDistributionPage;
use App\Models\DocumentSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('documents');
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

it('menyimpan pengajuan KP dari halaman sebagai pending', function () {
    $member = User::factory()->create([
        'email' => '210170030@mhs.unimal.ac.id',
        'is_approved' => true,
    ]);

    actingAs($member);

    Livewire::test(DocumentDistributionPage::class)
        ->set('data.kp', [
            'title' => 'Laporan Kerja Praktik Halaman',
            'company_name' => 'PT Contoh',
            'academic_advisor' => 'Dosen Pembimbing',
            'year' => 2026,
            'abstract' => 'Abstrak pengujian jalur tulis produksi halaman.',
        ])
        ->call('submitKp');

    $submission = DocumentSubmission::query()
        ->where('user_id', $member->id)
        ->where('type', DocumentSubmission::TYPE_INTERNSHIP_REPORT)
        ->first();

    expect($submission)->not->toBeNull()
        ->and($submission->title)->toBe('Laporan Kerja Praktik Halaman')
        ->and($submission->status)->toBe(DocumentSubmission::STATUS_PENDING);
});

it('menolak mengubah pengajuan KP yang sudah disetujui dari halaman', function () {
    $member = User::factory()->create([
        'email' => '210170031@mhs.unimal.ac.id',
        'is_approved' => true,
    ]);

    actingAs($member);

    DocumentSubmission::factory()->create([
        'user_id' => $member->id,
        'type' => DocumentSubmission::TYPE_INTERNSHIP_REPORT,
        'status' => DocumentSubmission::STATUS_APPROVED,
        'title' => 'Judul Resmi Disetujui',
    ]);

    Livewire::test(DocumentDistributionPage::class)
        ->set('data.kp', [
            'title' => 'Judul Percobaan Timpa',
            'company_name' => 'PT Contoh',
            'academic_advisor' => 'Dosen Pembimbing',
            'year' => 2026,
            'abstract' => 'Percobaan menimpa data yang sudah disetujui.',
        ])
        ->call('submitKp');

    expect(DocumentSubmission::query()
        ->where('user_id', $member->id)
        ->where('type', DocumentSubmission::TYPE_INTERNSHIP_REPORT)
        ->value('title'))->toBe('Judul Resmi Disetujui');
});

it('menyimpan pengajuan skripsi dari halaman sebagai pending', function () {
    $member = User::factory()->create([
        'email' => '210170032@mhs.unimal.ac.id',
        'is_approved' => true,
    ]);

    actingAs($member);

    Livewire::test(DocumentDistributionPage::class)
        ->set('data.skripsi', [
            'title' => 'Skripsi Pengujian Halaman',
            'academic_advisor' => 'Dosen Pembimbing',
            'year' => 2026,
            'abstract' => 'Abstrak pengujian jalur tulis skripsi.',
        ])
        ->call('submitSkripsi');

    expect(DocumentSubmission::query()
        ->where('user_id', $member->id)
        ->where('type', DocumentSubmission::TYPE_SKRIPSI)
        ->value('status'))->toBe(DocumentSubmission::STATUS_PENDING);
});

it('menyimpan sumbangan buku secara batch tanpa satu query per buku', function () {
    $member = User::factory()->create([
        'email' => '210170020@mhs.unimal.ac.id',
        'is_approved' => true,
    ]);

    actingAs($member);

    // Hitung SELECT ke tabel document_submissions selama penyimpanan saja,
    // agar overhead mount() tidak ikut terhitung.
    $captured = [];
    DB::listen(function ($query) use (&$captured): void {
        $sql = strtolower(trim($query->sql));
        if (str_starts_with($sql, 'select') && str_contains($sql, 'document_submissions')) {
            $captured[] = $query->sql;
        }
    });

    $submit = function (int $count) use (&$captured): int {
        $captured = [];

        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            // Filament Repeater menyimpan item dengan kunci string (UUID).
            $items["item-{$i}"] = ['title' => "Buku Sumbangan {$i}", 'copies_count' => 1];
        }

        Livewire::test(DocumentDistributionPage::class)
            ->set('data.books.items', $items)
            ->call('submitBooks')
            ->assertHasNoErrors();

        return count($captured);
    };

    $forFive = $submit(5);
    $forTen = $submit(10);

    // Kunci bukti: jumlah SELECT tetap sama saat jumlah buku bertambah.
    // Pola N+1 lama menjalankan satu SELECT per buku (5 → 10).
    expect($forFive)->toBeLessThan(5)
        ->and($forTen)->toBe($forFive);

    expect(DocumentSubmission::query()
        ->where('type', DocumentSubmission::TYPE_BOOK_DONATION)
        ->count())->toBe(15);
});

it('tidak menimpa pengajuan yang sudah disetujui lewat submitBooks dengan id lintas tipe', function () {
    $member = User::factory()->create([
        'email' => '210170022@mhs.unimal.ac.id',
        'is_approved' => true,
    ]);

    actingAs($member);

    // Pengajuan KP yang sudah disetujui. Bila id-nya dikirim lewat form
    // sumbangan buku, jalur tulis buku tidak boleh menimpanya.
    $approvedKp = DocumentSubmission::factory()->create([
        'user_id' => $member->id,
        'type' => DocumentSubmission::TYPE_INTERNSHIP_REPORT,
        'status' => DocumentSubmission::STATUS_APPROVED,
        'title' => 'Laporan KP Resmi Disetujui',
    ]);

    Livewire::test(DocumentDistributionPage::class)
        ->set('data.books.items', [
            'item-x' => ['id' => $approvedKp->id, 'title' => 'Percobaan Timpa', 'copies_count' => 1],
        ])
        ->call('submitBooks');

    $after = DocumentSubmission::query()->find($approvedKp->id);

    expect($after->title)->toBe('Laporan KP Resmi Disetujui')
        ->and($after->type)->toBe(DocumentSubmission::TYPE_INTERNSHIP_REPORT)
        ->and($after->status)->toBe(DocumentSubmission::STATUS_APPROVED);
});

it('membatalkan seluruh batch bila satu item gagal disimpan', function () {
    $member = User::factory()->create([
        'email' => '210170021@mhs.unimal.ac.id',
        'is_approved' => true,
    ]);

    actingAs($member);

    $items = [
        'item-a' => ['title' => 'Buku Pertama', 'copies_count' => 1],
        'item-b' => ['title' => 'Buku Kedua', 'copies_count' => 1],
    ];

    // Gagalkan item kedua tepat saat disimpan. Karena seluruh batch berada
    // dalam satu transaksi, item pertama harus ikut dibatalkan.
    DocumentSubmission::creating(function (DocumentSubmission $submission): void {
        if ($submission->title === 'Buku Kedua') {
            throw new RuntimeException('Simulasi kegagalan item kedua');
        }
    });

    $failed = false;
    try {
        Livewire::test(DocumentDistributionPage::class)
            ->set('data.books.items', $items)
            ->call('submitBooks');
    } catch (Throwable) {
        $failed = true;
    }

    // Tanpa transaksi, "Buku Pertama" akan tertinggal tersimpan (count = 1).
    expect($failed)->toBeTrue()
        ->and(DocumentSubmission::query()
            ->where('user_id', $member->id)
            ->where('type', DocumentSubmission::TYPE_BOOK_DONATION)
            ->count())->toBe(0);
});
