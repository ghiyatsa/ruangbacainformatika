<?php

use App\Filament\Widgets\ContactMessagesTableWidget;
use App\Filament\Widgets\LoanActivityChartWidget;
use App\Filament\Widgets\OperationsOverviewWidget;
use App\Filament\Widgets\OverdueLoanTableWidget;
use App\Filament\Widgets\SimilaritySyncOverviewWidget;
use App\Filament\Widgets\TodayVisitorsWidget;

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
        ->and(widgetProperty(ContactMessagesTableWidget::class, 'heading'))->toBe('Korespondensi Baru')
        ->and(widgetProperty(OverdueLoanTableWidget::class, 'heading'))->toBe('Pinjaman Terlambat');
});

it('uses concise descriptions on overview widgets', function () {
    expect(widgetProperty(OperationsOverviewWidget::class, 'description'))->toBe('Angka utama hari ini.')
        ->and(widgetProperty(SimilaritySyncOverviewWidget::class, 'description'))->toBe('Status sinkronisasi skripsi.')
        ->and(widgetProperty(LoanActivityChartWidget::class, 'description'))->toBe('Peminjaman dan kunjungan 7 hari.')
        ->and(widgetProperty(TodayVisitorsWidget::class, 'description'))->toBe('Ringkasan pengunjung hari ini.');
});
