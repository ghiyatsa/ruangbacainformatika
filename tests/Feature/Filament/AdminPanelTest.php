<?php

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
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
use App\Models\VisitLog;
use Illuminate\Support\Carbon;
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
        ->assertSee('Logo Situs')
        ->assertSee('Open Graph Image')
        ->assertSee('Favicon PNG')
        ->assertSee('WhatsApp Bantuan')
        ->assertSee('Nomor yang ditampilkan sebagai kontak bantuan.')
        ->assertSee('Simpan');
});

it('super admin users can render the library settings form', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get(route('filament.admin.settings.pages.library'))
        ->assertOk()
        ->assertSee('Maksimal Buku Dipinjam')
        ->assertSee('Durasi Peminjaman (Hari Kerja)')
        ->assertSee('Jumlah pinjaman aktif maksimal per anggota.')
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
        ->assertSee('Biarkan kosong jika PIN tidak diubah.');
});

it('super admin users can render the create user form for google accounts', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/users/create')
        ->assertOk()
        ->assertSee('Pengguna akan masuk dengan akun Google menggunakan email ini.')
        ->assertSee('WhatsApp')
        ->assertSee('Alamat')
        ->assertSee('Peran')
        ->assertSee('Pilih peran sesuai akses yang dibutuhkan.')
        ->assertSee('Aktifkan jika akun anggota sudah diperiksa dan siap digunakan.');
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
        ->assertSee('Pilih penerbit yang sudah ada atau tambah baru.')
        ->assertSee('Pilih penulis yang sudah ada atau tambah baru.')
        ->assertSee('Pilih kategori yang sudah ada atau tambah baru.')
        ->assertSee('Isi angka tanpa spasi atau tanda lain.')
        ->assertSee('Gunakan 4 digit tahun.')
        ->assertSee('Gunakan JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.');
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
        ->assertSee('Hanya terlambat');

    actingAs($user)
        ->get('/admin/visit-logs')
        ->assertOk()
        ->assertSee('Kunjungan')
        ->assertSee('Data kunjungan akan tampil di sini.')
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
        ->assertSee('Disetujui Hari Ini');
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
        ->assertSee('Pesan dari halaman kontak akan tampil di sini.');

    actingAs($user)
        ->get('/admin/catalog-reports')
        ->assertOk()
        ->assertSee('Belum ada laporan katalog')
        ->assertSee('Laporan dari halaman detail katalog akan tampil di sini.');

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
        ->get('/admin?tab=messages')
        ->assertOk()
        ->assertSee('Pesan dari halaman kontak akan tampil di sini.');
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
