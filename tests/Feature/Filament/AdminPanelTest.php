<?php

use App\Filament\Resources\Authors\AuthorResource;
use App\Filament\Resources\Books\BookResource;
use App\Filament\Resources\CatalogReports\CatalogReportResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\InternshipReports\InternshipReportResource;
use App\Filament\Resources\Loans\LoanResource;
use App\Filament\Resources\Skripsis\SkripsiResource;
use App\Filament\Resources\Theses\ThesisResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\VisitLogs\VisitLogResource;
use App\Models\Book;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
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
        ->assertOk();
});

it('super admin users can access the admin users resource', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/users')
        ->assertOk();
});

it('super admin users can render the general settings form', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get(route('filament.admin.settings.pages.general-settings'))
        ->assertOk()
        ->assertSee('Pengaturan Umum')
        ->assertSee('Nama Situs')
        ->assertSee('Tagline')
        ->assertSee('WhatsApp Bantuan')
        ->assertSee('Nomor kontak bantuan.')
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
        ->assertSee('Dihitung dalam hari kerja.')
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
        ->assertSee('Kosongkan jika tidak diubah.');
});

it('super admin users can render the create user form with a password field', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/users/create')
        ->assertOk()
        ->assertSee('Kata Sandi')
        ->assertSee('Peran')
        ->assertSee('Minimal 8 karakter.')
        ->assertSee('Pilih sesuai hak akses.')
        ->assertSee('Aktifkan jika akun siap dipakai.');
});

it('super admin users can render book relation helpers on the create book form', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/books/create')
        ->assertOk()
        ->assertSee('Pilih atau tambah penerbit.')
        ->assertSee('Pilih atau tambah penulis.')
        ->assertSee('Pilih atau tambah kategori.')
        ->assertSee('Gunakan angka saja.')
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
        ->assertSee('Hapus Terpilih');

    actingAs($user)
        ->get('/admin/loans')
        ->assertOk()
        ->assertSee('Hanya pinjaman aktif')
        ->assertSee('Hanya lewat jatuh tempo');

    actingAs($user)
        ->get('/admin/visit-logs')
        ->assertOk()
        ->assertSee('Kunjungan')
        ->assertSee('Data kunjungan akan tampil di sini.')
        ->assertSee('Ekspor');
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
        ->assertSee('Sync Similarity')
        ->assertSee('Gagal')
        ->assertSee('Perlu diproses')
        ->assertSee('Belum dijadwalkan')
        ->assertSee('Sinkronkan Terpilih');

    actingAs($user)
        ->get("/admin/skripsis/{$skripsi->getKey()}")
        ->assertOk()
        ->assertSee('Data Skripsi')
        ->assertSee('Sinkronisasi Similarity')
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
        ->assertSee('Sinkronisasi Similarity')
        ->assertSee('Sinkron Berhasil')
        ->assertSee('Perlu Tindak Lanjut')
        ->assertSee('Sedang Diproses')
        ->assertSee('Belum Dijadwalkan');
});

it('filament resources expose consistent navigation metadata', function () {
    expect(BookResource::getNavigationBadgeColor())->toBe('gray')
        ->and(BookResource::getNavigationBadgeTooltip())->toBe('Total buku')
        ->and(UserResource::getNavigationBadgeColor())->toBe('primary')
        ->and(LoanResource::getNavigationBadgeColor())->toBe('warning')
        ->and(LoanResource::getNavigationBadgeTooltip())->toBe('Total pinjaman aktif')
        ->and(ContactMessageResource::getNavigationBadgeColor())->toBe('warning')
        ->and(ContactMessageResource::getNavigationBadgeTooltip())->toBe('Korespondensi baru')
        ->and(VisitLogResource::getNavigationBadgeColor())->toBe('primary')
        ->and(VisitLogResource::getNavigationBadgeTooltip())->toBe('Kunjungan hari ini')
        ->and(AuthorResource::getNavigationBadgeColor())->toBe('gray')
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
    '/admin/publishers',
    '/admin/loans',
    '/admin/visit-logs',
    '/admin/settings/kiosk',
    '/admin/settings/general-settings',
]);

it('super admin users can render concise empty state copy on book management resources', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/contact-messages')
        ->assertOk()
        ->assertSee('Belum ada korespondensi')
        ->assertSee('Pesan dari halaman kontak akan tampil di sini.');

    actingAs($user)
        ->get('/admin/catalog-reports')
        ->assertOk()
        ->assertSee('Belum ada umpan balik katalog')
        ->assertSee('Masukan dari halaman detail katalog akan tampil di sini.');

    actingAs($user)
        ->get('/admin/books')
        ->assertOk()
        ->assertSee('Data buku akan tampil di sini.');

    actingAs($user)
        ->get('/admin/authors')
        ->assertOk()
        ->assertSee('Data penulis akan tampil di sini.');

    actingAs($user)
        ->get('/admin/categories')
        ->assertOk()
        ->assertSee('Data kategori akan tampil di sini.');

    actingAs($user)
        ->get('/admin/publishers')
        ->assertOk()
        ->assertSee('Data penerbit akan tampil di sini.');

    actingAs($user)
        ->get('/admin/skripsis')
        ->assertOk()
        ->assertSee('Data skripsi akan tampil di sini.');

    actingAs($user)
        ->get('/admin/theses')
        ->assertOk()
        ->assertSee('Data tesis akan tampil di sini.');

    actingAs($user)
        ->get('/admin/internship-reports')
        ->assertOk()
        ->assertSee('Data laporan KP akan tampil di sini.');
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

it('filament catalog report resource exposes pending navigation badge metadata', function () {
    expect(CatalogReportResource::getNavigationBadgeColor())->toBe('warning')
        ->and(CatalogReportResource::getNavigationBadgeTooltip())->toBe('Umpan balik menunggu tindak lanjut');
});

it('super admin users can see the contact messages widget on the dashboard', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Korespondensi Baru')
        ->assertSee('Pesan dari halaman kontak muncul di sini.');
});
