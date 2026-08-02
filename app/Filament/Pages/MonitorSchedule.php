<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ScheduledTasksTableWidget;
use App\Filament\Widgets\ScheduleOverviewWidget;
use App\Filament\Widgets\ScheduleRunLogTableWidget;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MonitorSchedule extends Page
{
    protected static ?string $navigationLabel = 'Monitor Penjadwalan';

    protected static ?string $title = 'Monitor Penjadwalan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static UnitEnum|string|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 92;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'md' => 2,
                    'xl' => 5,
                ])
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                        ScheduleOverviewWidget::class,
                    ])),
                Grid::make(1)
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                        ScheduledTasksTableWidget::class,
                    ])),
                Grid::make(1)
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                        ScheduleRunLogTableWidget::class,
                    ])),
            ]);
    }
}
