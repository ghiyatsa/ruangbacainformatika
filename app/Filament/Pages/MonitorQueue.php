<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FailedJobsTableWidget;
use App\Filament\Widgets\QueueOverviewWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MonitorQueue extends Page
{
    protected static ?string $navigationLabel = 'Monitor Antrian';

    protected static ?string $title = 'Monitor Antrian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static UnitEnum|string|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 91;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'md' => 2,
                    'xl' => 3,
                ])
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                        QueueOverviewWidget::class,
                    ])),
                Grid::make(1)
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                        FailedJobsTableWidget::class,
                    ])),
            ]);
    }
}
