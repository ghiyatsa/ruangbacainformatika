<?php

use App\Filament\Resources\Users\Widgets\RestrictedBorrowersOverviewWidget;
use App\Filament\Widgets\ContactMessagesTableWidget;
use App\Filament\Widgets\LoanActivityChartWidget;
use App\Filament\Widgets\OperationsOverviewWidget;
use App\Filament\Widgets\OverdueLoanTableWidget;
use App\Filament\Widgets\PendingMemberApprovalsWidget;
use App\Filament\Widgets\ServerInfoWidget;
use App\Filament\Widgets\SimilaritySyncOverviewWidget;
use App\Filament\Widgets\TodayVisitorsWidget;

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
    expect(widgetProperty(OperationsOverviewWidget::class, 'heading'))->toBe('Ringkasan Operasional')
        ->and(widgetProperty(SimilaritySyncOverviewWidget::class, 'heading'))->toBe('Sinkronisasi Similarity')
        ->and(widgetProperty(LoanActivityChartWidget::class, 'heading'))->toBe('Aktivitas Mingguan')
        ->and(widgetProperty(TodayVisitorsWidget::class, 'heading'))->toBe('Kunjungan Hari Ini')
        ->and(widgetProperty(ContactMessagesTableWidget::class, 'heading'))->toBe('Pesan Kontak Terbaru')
        ->and(widgetProperty(OverdueLoanTableWidget::class, 'heading'))->toBe('Pinjaman Terlambat')
        ->and(widgetProperty(ServerInfoWidget::class, 'heading'))->toBe('Informasi Server')
        ->and(widgetProperty(RestrictedBorrowersOverviewWidget::class, 'heading'))->toBe('Akses Peminjaman Anggota');
});

it('uses concise descriptions on overview widgets', function () {
    expect(widgetProperty(OperationsOverviewWidget::class, 'description'))->toBe('Ringkasan utama untuk hari ini.')
        ->and(widgetProperty(SimilaritySyncOverviewWidget::class, 'description'))->toBe('Ringkasan sinkronisasi data skripsi.')
        ->and(widgetProperty(LoanActivityChartWidget::class, 'description'))->toBe('Tren peminjaman dan kunjungan 7 hari terakhir.')
        ->and(widgetProperty(TodayVisitorsWidget::class, 'description'))->toBe('Ringkasan kunjungan hari ini.')
        ->and(widgetProperty(ServerInfoWidget::class, 'description'))->toBe('Pantau runtime dan konfigurasi dasar panel admin.')
        ->and(widgetProperty(RestrictedBorrowersOverviewWidget::class, 'description'))->toBe('Pantau anggota yang sedang dibatasi atau masih dalam masa jeda keterlambatan.');
});

it('links pending member approval stats to filtered user tables', function () {
    $stats = invade(app(PendingMemberApprovalsWidget::class))->getStats();

    expect($stats[0]->getLabel())->toBe('Menunggu Persetujuan')
        ->and($stats[0]->getUrl())->toContain('tableFilters%5Bis_approved%5D%5Bvalue%5D=0')
        ->and($stats[0]->getUrl())->toContain('tableFilters%5Bmanual_approval%5D%5BisActive%5D=1')
        ->and($stats[1]->getLabel())->toBe('Daftar Hari Ini')
        ->and($stats[1]->getUrl())->toContain('tableFilters%5Bregistered_today%5D%5BisActive%5D=1')
        ->and($stats[2]->getLabel())->toBe('Disetujui Hari Ini')
        ->and($stats[2]->getUrl())->toContain('tableFilters%5Bapproved_today%5D%5BisActive%5D=1');
});

it('links restricted borrower stats to the matching user filters', function () {
    $stats = invade(app(RestrictedBorrowersOverviewWidget::class))->getStats();

    expect($stats[0]->getLabel())->toBe('Sedang Dibatasi')
        ->and($stats[0]->getUrl())->toContain('tableFilters%5Brestricted_borrowers%5D%5BisActive%5D=1')
        ->and($stats[1]->getLabel())->toBe('Terlambat Aktif')
        ->and($stats[1]->getUrl())->toContain('tableFilters%5Bactive_overdue_borrowers%5D%5BisActive%5D=1')
        ->and($stats[2]->getLabel())->toBe('Masa Jeda')
        ->and($stats[2]->getUrl())->toContain('tableFilters%5Blate_return_cooldown%5D%5BisActive%5D=1');
});

it('separates operational member growth from approval queue copy', function () {
    $operationsStats = invade(app(OperationsOverviewWidget::class))->getStats();
    $approvalStats = invade(app(PendingMemberApprovalsWidget::class))->getStats();

    expect($operationsStats[3]->getLabel())->toBe('Anggota Baru Bulan Ini')
        ->and($operationsStats[3]->getDescription())->toContain('terdaftar bulan ini')
        ->and($operationsStats[3]->getDescription())->not->toContain('menunggu persetujuan')
        ->and($approvalStats[0]->getDescription())->not->toContain('Google')
        ->and($approvalStats[1]->getDescription())->not->toContain('Google');
});
