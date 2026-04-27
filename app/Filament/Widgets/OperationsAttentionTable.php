<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\KioskDevices\Pages\EditKioskDevice;
use App\Filament\Resources\Loans\Pages\ViewLoan;
use App\Models\KioskDevice;
use App\Models\Loan;
use Filament\Actions\Action;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class OperationsAttentionTable extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?string $heading = 'Butuh Tindakan';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records($this->records(...))
            ->paginated(false)
            ->defaultSort('priority', 'desc')
            ->searchable(false)
            ->columns([
                TextColumn::make('priority_label')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Kritis' => 'danger',
                        'Tinggi' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('type')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Loan' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('title')
                    ->label('Item')
                    ->searchable()
                    ->weight(fn (array $record): string => $record['priority'] >= 300 ? 'semibold' : 'medium')
                    ->color(fn (array $record): string => $record['priority'] >= 300 ? 'danger' : 'gray')
                    ->url(fn (array $record): string => $record['url'])
                    ->openUrlInNewTab(false),
                TextColumn::make('detail')
                    ->label('Detail')
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Melewati jatuh tempo' => 'danger',
                        'Menunggu approval' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->since(),
            ])
            ->recordClasses(fn (array $record): array => match ($record['priority']) {
                300 => ['border-s-4 border-danger-500 bg-danger-50/60 dark:bg-danger-950/20'],
                200 => ['border-s-4 border-warning-500 bg-warning-50/60 dark:bg-warning-950/20'],
                default => ['border-s-4 border-gray-300 bg-gray-50 dark:bg-white/5'],
            })
            ->recordActions([
                Action::make('open')
                    ->label('Buka')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->iconPosition(IconPosition::After)
                    ->color(fn (array $record): string => match ($record['priority']) {
                        300 => 'danger',
                        200 => 'warning',
                        default => 'gray',
                    })
                    ->url(fn (array $record): string => $record['url']),
            ])
            ->emptyStateHeading('Belum ada item yang perlu tindakan')
            ->emptyStateDescription('Dashboard ini akan menampilkan peminjaman lama dan kiosk yang bermasalah.');
    }

    protected function getTableDescription(): ?string
    {
        return 'Daftar cepat item operasional yang sebaiknya segera ditangani.';
    }

    protected function records(): Collection
    {
        $overdueLoans = Loan::query()
            ->with(['user', 'items.bookItem.book'])
            ->where('status', Loan::STATUS_BORROWED)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->orderBy('due_at')
            ->limit(5)
            ->get()
            ->map(function (Loan $loan): array {
                $bookSummary = $loan->items
                    ->pluck('bookItem.book.title')
                    ->filter()
                    ->unique()
                    ->take(2)
                    ->implode(', ');

                return [
                    'type' => 'Loan',
                    'title' => $loan->user?->name ?? 'Member tidak diketahui',
                    'detail' => filled($bookSummary)
                        ? "{$bookSummary} | {$loan->user?->email}"
                        : (string) ($loan->user?->email ?? 'Tanpa detail buku'),
                    'status' => 'Melewati jatuh tempo',
                    'updated_at' => $loan->due_at ?? $loan->borrowed_at,
                    'priority' => 300,
                    'priority_label' => 'Kritis',
                    'url' => ViewLoan::getUrl(['record' => $loan], panel: 'admin'),
                ];
            });

        $pendingKiosks = KioskDevice::query()
            ->where('status', KioskDevice::STATUS_PENDING)
            ->latest('created_at')
            ->limit(3)
            ->get()
            ->map(fn (KioskDevice $device): array => [
                'type' => 'Kiosk',
                'title' => $device->name ?: 'Perangkat tanpa nama',
                'detail' => "Kiosk ID: {$device->kiosk_identifier}",
                'status' => 'Menunggu approval',
                'updated_at' => $device->last_seen_at ?? $device->created_at,
                'priority' => 200,
                'priority_label' => 'Tinggi',
                'url' => EditKioskDevice::getUrl(['record' => $device], panel: 'admin'),
            ]);

        $offlineKiosks = KioskDevice::query()
            ->where('status', KioskDevice::STATUS_APPROVED)
            ->where(fn ($query) => $query
                ->whereNull('last_seen_at')
                ->orWhere('last_seen_at', '<', now()->subHour()))
            ->orderByDesc('last_seen_at')
            ->limit(3)
            ->get()
            ->map(fn (KioskDevice $device): array => [
                'type' => 'Kiosk',
                'title' => $device->name ?: 'Perangkat tanpa nama',
                'detail' => "Kiosk ID: {$device->kiosk_identifier}",
                'status' => 'Offline > 1 jam',
                'updated_at' => $device->last_seen_at ?? $device->updated_at,
                'priority' => 100,
                'priority_label' => 'Sedang',
                'url' => EditKioskDevice::getUrl(['record' => $device], panel: 'admin'),
            ]);

        return $overdueLoans
            ->concat($pendingKiosks)
            ->concat($offlineKiosks)
            ->sortByDesc('priority')
            ->values();
    }
}
