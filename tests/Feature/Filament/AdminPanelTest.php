<?php

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\Authors\AuthorResource;
use App\Filament\Resources\Books\BookResource;
use App\Filament\Resources\Books\Pages\ListBooks;
use App\Filament\Resources\CatalogReports\CatalogReportResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\InternshipReports\InternshipReportResource;
use App\Filament\Resources\Loans\LoanResource;
use App\Filament\Resources\PostCategories\PostCategoryResource;
use App\Filament\Resources\Skripsis\SkripsiResource;
use App\Filament\Resources\Theses\ThesisResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\VisitLogs\VisitLogResource;
use App\Models\Author;
use App\Models\Book;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Models\User;
use App\Models\VisitLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function makeSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('guests are redirected away from the admin panel', function () {
    get('/admin')->assertRedirect(route('login'));
});

it('non admin users can not access the admin panel', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('super admin users can access the admin dashboard', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Ringkasan')
        ->assertSee('Aktivitas')
        ->assertSee('Pesan')
        ->assertSee('Server');
});

it('super admin users can access the admin users resource', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Sedang Dibatasi')
        ->assertSee('Terlambat Aktif')
        ->assertSee('Masa Jeda')
        ->assertSee('WhatsApp')
        ->assertSee('Alamat')
        ->assertSee('Setujui')
        ->assertSee('Setujui Terpilih');
});

it('super admin users can render the general settings form', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get(route('filament.admin.settings.pages.general-settings'))
        ->assertOk()
        ->assertSee('Pengaturan Umum')
        ->assertSee('Nama Situs')
        ->assertSee('Tagline')
        ->assertSee('Deskripsi Situs')
        ->assertSee('Kata Kunci SEO')
        ->assertSee('WhatsApp Bantuan')
        ->assertSee('Nomor kontak layanan.')
        ->assertDontSee('Branding & Icon')
        ->assertDontSee('Logo Situs')
        ->assertDontSee('Open Graph Image')
        ->assertDontSee('Favicon PNG')
        ->assertSee('Simpan');
});

it('super admin users can render the library settings form', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get(route('filament.admin.settings.pages.library'))
        ->assertOk()
        ->assertSee('Maksimal Buku Dipinjam')
        ->assertSee('Durasi Peminjaman (Hari Kerja)')
        ->assertSee('Batas pinjaman aktif per anggota.')
        ->assertSee('Durasi dihitung dalam hari kerja.')
        ->assertSee('Simpan');
});

it('super admin users can render the kiosk settings actions', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/settings/kiosk')
        ->assertOk()
        ->assertSee('Pengaturan Kios')
        ->assertSee('Reset Sesi Perangkat')
        ->assertSee('Perangkat Aktif')
        ->assertSee('PIN Kios')
        ->assertSee('Kosongkan jika PIN tidak diubah.')
        ->assertSee('Kosongkan untuk membuka akses ke semua jaringan. Pisahkan IP atau CIDR dengan baris baru atau koma.');
});

it('super admin users can render concise integration settings copy', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/settings/integrasi')
        ->assertOk()
        ->assertSee('Pengaturan Integrasi')
        ->assertSee('Pengaturan WhatsApp untuk notifikasi rutin.')
        ->assertSee('Isi 0 jika jeda tidak diperlukan.')
        ->assertSee('Sinkronkan Ulang Semua Skripsi')
        ->assertSee('Samakan Status dari Index API');
});

it('super admin users can render the create user form for google accounts', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/users/create')
        ->assertOk()
        ->assertSee('Email ini digunakan untuk masuk dengan Google.')
        ->assertSee('WhatsApp')
        ->assertSee('Alamat')
        ->assertSee('Peran')
        ->assertSee('Pilih peran sesuai kewenangan akun.')
        ->assertSee('Tandai jika akun sudah lolos review awal. Akses pinjam tetap menunggu verifikasi WhatsApp dan peran anggota.');
});

it('super admin users see verified whatsapp as locked on the edit user form', function () {
    $admin = makeSuperAdmin();
    $managedUser = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);

    $response = actingAs($admin)
        ->get("/admin/users/{$managedUser->getKey()}/edit")
        ->assertOk();

    $content = $response->getContent();

    expect($content)->toContain('wire:model="data.whatsapp"')
        ->and($content)->toMatch('/wire:model="data\\.whatsapp"[^>]*disabled|disabled[^>]*wire:model="data\\.whatsapp"/');
});

it('super admin users can render book relation helpers on the create book form', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/books/create')
        ->assertOk()
        ->assertSee('Pilih penerbit atau tambahkan data baru.')
        ->assertSee('Pilih penulis atau tambahkan data baru.')
        ->assertSee('Pilih kategori atau tambahkan data baru.')
        ->assertSee('Gunakan untuk buku biasa. Saat diisi, jalur ISSN disembunyikan.')
        ->assertSee('Gunakan untuk jurnal atau serial. Saat diisi, jalur ISBN disembunyikan.')
        ->assertDontSee('Isi jumlah atau rentang halaman utama.')
        ->assertSee('Gunakan 4 digit tahun.')
        ->assertSee('JPG, PNG, atau WEBP. Maksimal 2 MB.');
});

it('super admin users can render concise table filter and bulk action labels', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/books')
        ->assertOk()
        ->assertSee('Publikasi')
        ->assertSee('Unggulan')
        ->assertSee('Peminjaman')
        ->assertSee('Hanya stok habis')
        ->assertSee('Tanpa sampul')
        ->assertSee('Tandai Unggulan')
        ->assertSee('Aktifkan Pinjam')
        ->assertSee('Hapus Terpilih')
        ->assertSee('Ekspor');

    actingAs($user)
        ->get('/admin/loans')
        ->assertOk()
        ->assertSee('Hanya pinjaman aktif')
        ->assertSee('Hanya terlambat');

    actingAs($user)
        ->get('/admin/visit-logs')
        ->assertOk()
        ->assertSee('Kunjungan')
        ->assertSee('Data kunjungan akan muncul di sini.')
        ->assertSee('Ekspor');
});

it('super admin users see visit times in admin timezone', function () {
    $user = makeSuperAdmin();

    VisitLog::query()->create([
        'name' => 'Pengunjung Zona Waktu',
        'visitor_type' => VisitLog::VISITOR_TYPE_MAHASISWA,
        'purpose' => 'read',
        'visited_at' => Carbon::parse('2026-05-28 17:30:00', 'UTC'),
    ]);

    actingAs($user)
        ->get('/admin/visit-logs')
        ->assertOk()
        ->assertSee('00:30')
        ->assertDontSee('17:30');
});

it('visit log navigation badge counts visits using the admin timezone day boundary', function () {
    VisitLog::query()->create([
        'name' => 'Pengunjung Awal Hari',
        'visitor_type' => VisitLog::VISITOR_TYPE_MAHASISWA,
        'purpose' => 'read',
        'visited_at' => Carbon::parse('2026-05-28 17:30:00', 'UTC'),
    ]);

    VisitLog::query()->create([
        'name' => 'Pengunjung Hari Sebelumnya',
        'visitor_type' => VisitLog::VISITOR_TYPE_MAHASISWA,
        'purpose' => 'read',
        'visited_at' => Carbon::parse('2026-05-28 16:30:00', 'UTC'),
    ]);

    Carbon::setTestNow(Carbon::parse('2026-05-29 00:45:00', 'Asia/Jakarta'));

    expect(VisitLogResource::getNavigationBadge())->toBe('1');

    Carbon::setTestNow();
});

it('super admin users can render consistent resource headings', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/loans')
        ->assertOk()
        ->assertSee('Peminjaman');

    actingAs($user)
        ->get('/admin/skripsis')
        ->assertOk()
        ->assertSee('Skripsi');

    actingAs($user)
        ->get('/admin/theses')
        ->assertOk()
        ->assertSee('Tesis');

    actingAs($user)
        ->get('/admin/internship-reports')
        ->assertOk()
        ->assertSee('Laporan KP');
});

it('super admin users can monitor similarity sync status from skripsi admin pages', function () {
    Queue::fake();

    $user = makeSuperAdmin();
    $skripsi = Skripsi::factory()->create([
        'student_id' => '2301700420',
        'title' => 'Pemantauan sinkronisasi similarity pada admin',
    ]);

    SimilaritySyncStatus::query()->updateOrCreate(
        ['source_skripsi_id' => $skripsi->id],
        [
            'status' => SimilaritySyncStatus::STATUS_FAILED,
            'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
            'attempts' => 2,
            'last_error' => 'Token similarity tidak valid.',
            'last_attempt_at' => now(),
        ],
    );

    actingAs($user)
        ->get('/admin/skripsis')
        ->assertOk()
        ->assertSee('Status Similarity')
        ->assertSee('Gagal')
        ->assertSee('Perlu diproses')
        ->assertSee('Belum dijadwalkan')
        ->assertSee('Sinkronkan Terpilih');

    actingAs($user)
        ->get("/admin/skripsis/{$skripsi->getKey()}")
        ->assertOk()
        ->assertSee('Data Skripsi')
        ->assertSee('Sinkronisasi Similarity')
        ->assertSee('Proses terakhir')
        ->assertSee('Catatan error terakhir')
        ->assertSee('Token similarity tidak valid.');
});

it('super admin users can see similarity sync overview on the admin dashboard', function () {
    Queue::fake();

    $user = makeSuperAdmin();
    $skripsi = Skripsi::factory()->create([
        'student_id' => '2301700520',
    ]);

    SimilaritySyncStatus::query()->updateOrCreate(
        ['source_skripsi_id' => $skripsi->id],
        [
            'status' => SimilaritySyncStatus::STATUS_FAILED,
            'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
            'attempts' => 1,
            'last_error' => 'Bulk sinkronisasi ke Similarity API gagal.',
            'last_attempt_at' => now(),
        ],
    );

    actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Sinkron Berhasil')
        ->assertSee('Perlu Tindak Lanjut')
        ->assertSee('Sedang Diproses')
        ->assertSee('Belum Dijadwalkan');
});

it('super admin users can see pending member approvals overview on the admin dashboard', function () {
    $user = makeSuperAdmin();

    User::factory()->create([
        'auth_provider' => 'google',
        'email' => 'pending@unimal.ac.id',
        'is_approved' => false,
    ]);

    actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Menunggu Persetujuan')
        ->assertSee('Daftar Hari Ini')
        ->assertSee('Review Awal Hari Ini');
});

it('filament resources expose consistent navigation metadata', function () {
    expect(BookResource::getNavigationBadgeColor())->toBe('gray')
        ->and(BookResource::getNavigationBadgeTooltip())->toBe('Total buku')
        ->and(UserResource::getNavigationBadgeColor())->toBe('primary')
        ->and(LoanResource::getNavigationBadgeColor())->toBe('warning')
        ->and(LoanResource::getNavigationBadgeTooltip())->toBe('Total pinjaman aktif')
        ->and(ContactMessageResource::getNavigationBadgeColor())->toBe('warning')
        ->and(ContactMessageResource::getNavigationBadgeTooltip())->toBe('Pesan kontak baru')
        ->and(ActivityLogResource::canCreate())->toBeFalse()
        ->and(VisitLogResource::getNavigationBadgeColor())->toBe('primary')
        ->and(VisitLogResource::getNavigationBadgeTooltip())->toBe('Kunjungan hari ini')
        ->and(AuthorResource::getNavigationBadgeColor())->toBe('gray')
        ->and(PostCategoryResource::getNavigationBadgeColor())->toBe('gray')
        ->and(PostCategoryResource::getNavigationBadgeTooltip())->toBe('Total kategori')
        ->and(SkripsiResource::getNavigationBadgeColor())->toBe('gray')
        ->and(ThesisResource::getNavigationBadgeColor())->toBe('gray')
        ->and(InternshipReportResource::getNavigationBadgeColor())->toBe('gray');
});

it('super admin users are redirected from settings cluster to the first settings page', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/settings')
        ->assertRedirect(route('filament.admin.settings.pages.general-settings', absolute: false));
});

it('super admin users can access key admin resources', function (string $path) {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get($path)
        ->assertOk();
})->with([
    '/admin/books',
    '/admin/contact-messages',
    '/admin/catalog-reports',
    '/admin/authors',
    '/admin/categories',
    '/admin/post-categories',
    '/admin/publishers',
    '/admin/loans',
    '/admin/visit-logs',
    '/admin/logs',
    '/admin/activity-logs',
    '/admin/settings/kiosk',
    '/admin/settings/general-settings',
]);

it('super admin users can render concise empty state copy on book management resources', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/contact-messages')
        ->assertOk()
        ->assertSee('Belum ada pesan masuk')
        ->assertSee('Pesan kontak akan muncul di sini.');

    actingAs($user)
        ->get('/admin/catalog-reports')
        ->assertOk()
        ->assertSee('Belum ada laporan katalog')
        ->assertSee('Laporan katalog akan muncul di sini.');

    actingAs($user)
        ->get('/admin/books')
        ->assertOk()
        ->assertSee('Daftar buku akan muncul di sini.');

    actingAs($user)
        ->get('/admin/authors')
        ->assertOk()
        ->assertSee('Daftar penulis akan muncul di sini.');

    actingAs($user)
        ->get('/admin/categories')
        ->assertOk()
        ->assertSee('Daftar kategori akan muncul di sini.');

    actingAs($user)
        ->get('/admin/post-categories')
        ->assertOk()
        ->assertSee('Kategori akan tampil di sini.');

    actingAs($user)
        ->get('/admin/publishers')
        ->assertOk()
        ->assertSee('Daftar penerbit akan muncul di sini.');

    actingAs($user)
        ->get('/admin/skripsis')
        ->assertOk()
        ->assertSee('Data skripsi akan muncul di sini.');

    actingAs($user)
        ->get('/admin/theses')
        ->assertOk()
        ->assertSee('Data tesis akan muncul di sini.');

    actingAs($user)
        ->get('/admin/internship-reports')
        ->assertOk()
        ->assertSee('Data laporan KP akan muncul di sini.');
});

it('super admin users can access the books resource when some books have no published year', function () {
    $user = makeSuperAdmin();

    Book::factory()->create([
        'published_year' => null,
    ]);

    actingAs($user)
        ->get('/admin/books')
        ->assertOk();
});

it('super admin book detail follows the active identifier path', function () {
    $user = makeSuperAdmin();

    $journal = Book::factory()->create([
        'title' => 'Jurnal Informatika',
        'isbn' => null,
        'issn' => '1234-5678',
        'edition' => 'Vol. 12 No. 2',
        'pages' => '120-145',
    ]);

    actingAs($user)
        ->get("/admin/books/{$journal->getKey()}")
        ->assertOk()
        ->assertSee('ISSN')
        ->assertSee('Edisi / Volume')
        ->assertSee('120-145')
        ->assertDontSee('>ISBN<', false);
});

it('filament catalog report resource exposes pending navigation badge metadata', function () {
    expect(CatalogReportResource::getNavigationBadgeColor())->toBe('warning')
        ->and(CatalogReportResource::getNavigationBadgeTooltip())->toBe('Umpan balik menunggu tindak lanjut');
});

it('super admin users can see the contact messages and catalog reports widgets on the dashboard', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin?tab=messages')
        ->assertOk()
        ->assertSee('Pesan Kontak Terbaru')
        ->assertSee('Laporan Umpan Balik Katalog')
        ->assertSee('Pesan kontak akan muncul di sini.')
        ->assertSee('Laporan katalog akan muncul di sini.');
});

it('super admin users can see the server info widget on the dashboard', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin?tab=system')
        ->assertOk()
        ->assertSee('Mode Aplikasi')
        ->assertSee('Runtime')
        ->assertSee('Driver Layanan')
        ->assertSee('Penyimpanan')
        ->assertSee('Waktu Server');
});

it('can filter books by author', function () {
    $user = makeSuperAdmin();
    $author1 = Author::factory()->create(['name' => 'Author Alpha']);
    $author2 = Author::factory()->create(['name' => 'Author Beta']);

    $book1 = Book::factory()->create(['title' => 'Book Alpha Edition']);
    $book1->authors()->attach($author1);

    $book2 = Book::factory()->create(['title' => 'Book Beta Edition']);
    $book2->authors()->attach($author2);

    actingAs($user);

    Livewire::test(ListBooks::class)
        ->assertCanSeeTableRecords([$book1, $book2])
        ->filterTable('authors', [$author1->id])
        ->assertCanSeeTableRecords([$book1])
        ->assertCanNotSeeTableRecords([$book2]);
});
