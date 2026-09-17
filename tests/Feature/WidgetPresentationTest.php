<?php

use App\Filament\Resources\Users\Widgets\RestrictedBorrowersOverviewWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
use App\Filament\Resources\Skripsis\Pages\ListSkripsis;
use App\Filament\Widgets\CatalogReportsTableWidget;
use App\Filament\Widgets\ContactMessagesTableWidget;
use App\Filament\Widgets\OperationsOverviewWidget;
use App\Filament\Widgets\OverdueLoanTableWidget;
use App\Filament\Widgets\PendingMemberApprovalsWidget;
use App\Filament\Widgets\ServerInfoWidget;
use App\Filament\Widgets\SimilaritySyncOverviewWidget;
use App\Filament\Widgets\TodayVisitorsWidget;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Livewire\invade;

function widgetProperty(string $className, string $property): mixed
{
    $reflection = new ReflectionProperty($className, $property);

    if ($reflection->isStatic()) {
        return $reflection->getValue();
    }

    return $reflection->getValue(app($className));
}

it('uses concise headings across filament widgets', function () {
    expect(widgetProperty(OperationsOverviewWidget::class, 'heading'))->toBeNull()
        ->and(widgetProperty(SimilaritySyncOverviewWidget::class, 'heading'))->toBeNull()
        ->and(widgetProperty(TodayVisitorsWidget::class, 'heading'))->toBeNull()
        ->and(widgetProperty(ContactMessagesTableWidget::class, 'heading'))->toBe('Pesan Kontak Terbaru')
        ->and(widgetProperty(CatalogReportsTableWidget::class, 'heading'))->toBe('Laporan Umpan Balik Katalog')
        ->and(widgetProperty(OverdueLoanTableWidget::class, 'heading'))->toBeNull()
        ->and(widgetProperty(ServerInfoWidget::class, 'heading'))->toBeNull()
        ->and(widgetProperty(RestrictedBorrowersOverviewWidget::class, 'heading'))->toBeNull();
});

it('uses concise descriptions on overview widgets', function () {
    expect(widgetProperty(OperationsOverviewWidget::class, 'description'))->toBeNull()
        ->and(widgetProperty(SimilaritySyncOverviewWidget::class, 'description'))->toBeNull()
        ->and(widgetProperty(TodayVisitorsWidget::class, 'description'))->toBeNull()
        ->and(widgetProperty(ServerInfoWidget::class, 'description'))->toBeNull()
        ->and(widgetProperty(RestrictedBorrowersOverviewWidget::class, 'description'))->toBeNull();
});

it('links pending member approval stats to filtered user tables', function () {
    $stats = invade(app(PendingMemberApprovalsWidget::class))->getStats();

    expect($stats[0]->getLabel())->toBe('Menunggu Persetujuan Akun')
        ->and($stats[0]->getUrl())->toContain('filters%5Bis_approved%5D%5Bvalue%5D=0')
        ->and($stats[0]->getUrl())->toContain('filters%5Bmanual_approval%5D%5BisActive%5D=1')
        ->and($stats[1]->getLabel())->toBe('Pendaftar Hari Ini')
        ->and($stats[1]->getUrl())->toContain('filters%5Bregistered_today%5D%5BisActive%5D=1')
        ->and($stats[2]->getLabel())->toBe('Disetujui Hari Ini')
        ->and($stats[2]->getUrl())->toContain('filters%5Bapproved_today%5D%5BisActive%5D=1');
});

it('links restricted borrower stats to the matching user filters', function () {
    $stats = invade(app(RestrictedBorrowersOverviewWidget::class))->getStats();

    expect($stats[0]->getLabel())->toBe('Sedang Dibatasi')
        ->and($stats[0]->getUrl())->toContain('filters%5Brestricted_borrowers%5D%5BisActive%5D=1')
        ->and($stats[1]->getLabel())->toBe('Terlambat Aktif')
        ->and($stats[1]->getUrl())->toContain('filters%5Bactive_overdue_borrowers%5D%5BisActive%5D=1')
        ->and($stats[2]->getLabel())->toBe('Masa Jeda')
        ->and($stats[2]->getUrl())->toContain('filters%5Blate_return_cooldown%5D%5BisActive%5D=1');
});

it('keeps approval queue copy separate from operational stats', function () {
    // `pendingMemberApproval()` only counts campus emails that are not
    // auto-approved, so a plain non-campus address would not register here.
    User::factory()->count(2)->create([
        'email' => fn (): string => fake()->unique()->userName().'@mhs.unimal.ac.id',
        'is_approved' => false,
        'created_at' => now(),
    ]);

    $operationsStats = invade(app(OperationsOverviewWidget::class))->getStats();
    $approvalStats = invade(app(PendingMemberApprovalsWidget::class))->getStats();

    // Widget operasional tidak lagi memuat kartu pertumbuhan anggota; dua
    // widget tetap harus memakai istilah berbeda agar tidak saling kabur.
    expect($operationsStats)->toHaveCount(3)
        ->and($approvalStats[0]->getLabel())->toBe('Menunggu Persetujuan Akun')
        ->and($approvalStats[0]->getDescription())->not->toContain('Google')
        ->and($approvalStats[1]->getDescription())->not->toContain('Google');
});

it('counts similarity queue stats from active skripsi records only', function () {
    $activeSkripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());

    SimilaritySyncStatus::query()->create([
        'syncable_id' => $activeSkripsi->id,
        'syncable_type' => Skripsi::class,
        'status' => SimilaritySyncStatus::STATUS_PENDING,
        'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
        'attempts' => 1,
        'last_error' => 'Masih aktif',
    ]);

    SimilaritySyncStatus::query()->create([
        'syncable_id' => 999999,
        'syncable_type' => Skripsi::class,
        'status' => SimilaritySyncStatus::STATUS_PENDING,
        'last_operation' => SimilaritySyncStatus::OPERATION_DELETE,
        'attempts' => 1,
        'last_error' => 'Orphan',
    ]);

    $stats = invade(app(SimilaritySyncOverviewWidget::class))->getStats();

    // Hanya keberhasilan dan kegagalan yang ditampilkan; keduanya dihitung
    // dari karya aktif sehingga catatan yatim tidak ikut terhitung.
    expect($stats)->toHaveCount(2)
        ->and($stats[0]->getLabel())->toBe('Sinkron Berhasil')
        ->and($stats[1]->getLabel())->toBe('Sinkron Gagal')
        ->and($stats[0]->getValue())->toBe(0)
        ->and($stats[1]->getValue())->toBe(0);
});

it('filters skripsi that failed to sync from the list page', function () {
    $gagal = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());

    SimilaritySyncStatus::query()->create([
        'syncable_id' => $gagal->id,
        'syncable_type' => Skripsi::class,
        'status' => SimilaritySyncStatus::STATUS_FAILED,
        'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
        'attempts' => 1,
        'last_error' => 'Gagal',
    ]);

    $lolos = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());

    SimilaritySyncStatus::query()->create([
        'syncable_id' => $lolos->id,
        'syncable_type' => Skripsi::class,
        'status' => SimilaritySyncStatus::STATUS_SYNCED,
        'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
        'attempts' => 1,
    ]);

    $admin = User::factory()->create();
    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);
    $admin->assignRole($role);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($admin);

    // Filter harus menyaring hanya karya yang gagal disinkronkan.
    Livewire::test(ListSkripsis::class)
        ->filterTable('sinkron_gagal')
        ->assertCanSeeTableRecords([$gagal])
        ->assertCanNotSeeTableRecords([$lolos]);
});

it('filters skripsi that synced successfully from the list page', function () {
    $berhasil = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());

    SimilaritySyncStatus::query()->create([
        'syncable_id' => $berhasil->id,
        'syncable_type' => Skripsi::class,
        'status' => SimilaritySyncStatus::STATUS_SYNCED,
        'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
        'attempts' => 1,
    ]);

    $gagal = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());

    SimilaritySyncStatus::query()->create([
        'syncable_id' => $gagal->id,
        'syncable_type' => Skripsi::class,
        'status' => SimilaritySyncStatus::STATUS_FAILED,
        'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
        'attempts' => 1,
        'last_error' => 'Gagal',
    ]);

    $admin = User::factory()->create();
    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);
    $admin->assignRole($role);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($admin);

    // Kartu "Sinkron Berhasil" harus menyaring hanya karya yang tersinkron.
    Livewire::test(ListSkripsis::class)
        ->filterTable('sinkron_berhasil')
        ->assertCanSeeTableRecords([$berhasil])
        ->assertCanNotSeeTableRecords([$gagal]);
});
