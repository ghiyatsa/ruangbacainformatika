<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ContactMessagesTableWidget;
use App\Filament\Widgets\LoanActivityChartWidget;
use App\Filament\Widgets\OperationsOverviewWidget;
use App\Filament\Widgets\OverdueLoanTableWidget;
use App\Filament\Widgets\PendingMemberApprovalsWidget;
use App\Filament\Widgets\ServerInfoWidget;
use App\Filament\Widgets\SimilaritySyncOverviewWidget;
use App\Filament\Widgets\TodayVisitorsWidget;
use App\Models\ContactMessage;
use App\Models\Loan;
use App\Models\User;
use BackedEnum;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Livewire\Attributes\Url;

class Dashboard extends \Filament\Pages\Dashboard
{
    #[Url(as: 'tab')]
    public ?string $activeTab = null;

    protected static ?string $title = 'Dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Home;

    public function mount(): void
    {
        $this->activeTab = $this->normalizeActiveTab($this->activeTab);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Toolbar Dashboard')
                    ->key('dashboard-toolbar-tabs')
                    ->contained(false)
                    ->scrollable(false)
                    ->livewireProperty('activeTab')
                    ->tabs($this->getTabs()),
                Grid::make($this->getColumns())
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getActiveWidgets())),
            ]);
    }

    /**
     * @return int | array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'overview' => Tab::make('Ringkasan')
                ->icon(Heroicon::OutlinedRectangleGroup)
                ->badge(fn (): ?int => $this->pendingApprovalsCount())
                ->badgeColor('warning'),
            'activity' => Tab::make('Aktivitas')
                ->icon(Heroicon::OutlinedChartBar)
                ->badge(fn (): ?int => $this->overdueLoansCount())
                ->badgeColor('danger'),
            'messages' => Tab::make('Pesan')
                ->icon(Heroicon::OutlinedEnvelope)
                ->badge(fn (): ?int => $this->newMessagesCount())
                ->badgeColor('info'),
            'system' => Tab::make('Server')
                ->icon(Heroicon::OutlinedServerStack),
        ];
    }

    public function updatedActiveTab(?string $state): void
    {
        $this->activeTab = $this->normalizeActiveTab($state);
    }

    /**
     * @return array<class-string<Widget>>
     */
    protected function getActiveWidgets(): array
    {
        return $this->getWidgetGroups()[$this->normalizeActiveTab($this->activeTab)];
    }

    /**
     * @return array<string, array<class-string<Widget>>>
     */
    protected function getWidgetGroups(): array
    {
        return [
            'overview' => [
                OperationsOverviewWidget::class,
                PendingMemberApprovalsWidget::class,
                SimilaritySyncOverviewWidget::class,
            ],
            'activity' => [
                LoanActivityChartWidget::class,
                TodayVisitorsWidget::class,
                OverdueLoanTableWidget::class,
            ],
            'messages' => [
                ContactMessagesTableWidget::class,
            ],
            'system' => [
                ServerInfoWidget::class,
            ],
        ];
    }

    protected function normalizeActiveTab(?string $tab): string
    {
        $availableTabs = array_keys($this->getWidgetGroups());

        if (! in_array($tab, $availableTabs, true)) {
            return $availableTabs[0];
        }

        return $tab;
    }

    protected function pendingApprovalsCount(): ?int
    {
        $count = User::query()
            ->pendingMemberApproval()
            ->count();

        return $count > 0 ? $count : null;
    }

    protected function overdueLoansCount(): ?int
    {
        $count = Loan::query()
            ->where('status', Loan::STATUS_BORROWED)
            ->where('due_at', '<', now())
            ->count();

        return $count > 0 ? $count : null;
    }

    protected function newMessagesCount(): ?int
    {
        $count = ContactMessage::query()
            ->where('status', ContactMessage::STATUS_NEW)
            ->count();

        return $count > 0 ? $count : null;
    }
}
