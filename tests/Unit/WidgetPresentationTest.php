<?php

use App\Filament\Resources\Users\Widgets\RestrictedBorrowersOverviewWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
use App\Filament\Widgets\CatalogReportsTableWidget;
use App\Filament\Widgets\ContactMessagesTableWidget;
use App\Filament\Widgets\LoanActivityChartWidget;
use App\Filament\Widgets\OperationsOverviewWidget;
use App\Filament\Widgets\OverdueLoanTableWidget;
use App\Filament\Widgets\PendingMemberApprovalsWidget;
use App\Filament\Widgets\ServerInfoWidget;
use App\Filament\Widgets\SimilaritySyncOverviewWidget;
use App\Filament\Widgets\TodayVisitorsWidget;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;

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
        ->and(widgetProperty(LoanActivityChartWidget::class, 'heading'))->toBe('Aktivitas Mingguan')
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
        ->and(widgetProperty(LoanActivityChartWidget::class, 'description'))->toBe('Tren peminjaman dan kunjungan 7 hari terakhir.')
        ->and(widgetProperty(TodayVisitorsWidget::class, 'description'))->toBeNull()
        ->and(widgetProperty(ServerInfoWidget::class, 'description'))->toBeNull()
        ->and(widgetProperty(RestrictedBorrowersOverviewWidget::class, 'description'))->toBeNull();
});

it('links pending member approval stats to filtered user tables', function () {
    $stats = invade(app(PendingMemberApprovalsWidget::class))->getStats();

    expect($stats[0]->getLabel())->toBe('Menunggu Persetujuan')
        ->and($stats[0]->getUrl())->toContain('filters%5Bis_approved%5D%5Bvalue%5D=0')
        ->and($stats[0]->getUrl())->toContain('filters%5Bmanual_approval%5D%5BisActive%5D=1')
        ->and($stats[1]->getLabel())->toBe('Daftar Hari Ini')
        ->and($stats[1]->getUrl())->toContain('filters%5Bregistered_today%5D%5BisActive%5D=1')
        ->and($stats[2]->getLabel())->toBe('Review Awal Hari Ini')
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

it('separates operational member growth from approval queue copy', function () {
    $operationsStats = invade(app(OperationsOverviewWidget::class))->getStats();
    $approvalStats = invade(app(PendingMemberApprovalsWidget::class))->getStats();

    expect($operationsStats[3]->getLabel())->toBe('Anggota Baru Bulan Ini')
        ->and($operationsStats[3]->getDescription())->toContain('pendaftaran bulan ini')
        ->and($operationsStats[3]->getDescription())->not->toContain('menunggu persetujuan')
        ->and($approvalStats[0]->getDescription())->not->toContain('Google')
        ->and($approvalStats[1]->getDescription())->not->toContain('Google');
});

it('counts similarity overview stats from active skripsi records only', function () {
    $activeSkripsi = Skripsi::withoutEvents(fn (): Skripsi => Skripsi::factory()->create());

    SimilaritySyncStatus::query()->create([
        'syncable_id' => $activeSkripsi->id,
        'syncable_type' => Skripsi::class,
        'status' => SimilaritySyncStatus::STATUS_FAILED,
        'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
        'attempts' => 1,
        'last_error' => 'Masih aktif',
    ]);

    SimilaritySyncStatus::query()->create([
        'syncable_id' => 999999,
        'syncable_type' => Skripsi::class,
        'status' => SimilaritySyncStatus::STATUS_FAILED,
        'last_operation' => SimilaritySyncStatus::OPERATION_DELETE,
        'attempts' => 1,
        'last_error' => 'Orphan',
    ]);

    $stats = invade(app(SimilaritySyncOverviewWidget::class))->getStats();

    expect($stats[1]->getLabel())->toBe('Perlu Tindak Lanjut')
        ->and($stats[1]->getValue())->toBe(1)
        ->and($stats[3]->getValue())->toBe(0);
});
