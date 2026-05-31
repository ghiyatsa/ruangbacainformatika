<?php

namespace App\Providers\Filament;

use AchyutN\FilamentLogViewer\FilamentLogViewer;
use App\Filament\Widgets\ContactMessagesTableWidget;
use App\Filament\Widgets\LoanActivityChartWidget;
use App\Filament\Widgets\OperationsOverviewWidget;
use App\Filament\Widgets\OverdueLoanTableWidget;
use App\Filament\Widgets\PendingMemberApprovalsWidget;
use App\Filament\Widgets\SimilaritySyncOverviewWidget;
use App\Filament\Widgets\TodayVisitorsWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\Permission\Models\Role;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->spa()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->favicon('favicon.svg')
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->databaseNotifications()
            ->strictAuthorization()
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                OperationsOverviewWidget::class,
                PendingMemberApprovalsWidget::class,
                SimilaritySyncOverviewWidget::class,
                LoanActivityChartWidget::class,
                TodayVisitorsWidget::class,
                ContactMessagesTableWidget::class,
                OverdueLoanTableWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentLogViewer::make()
                    ->authorize(fn(): bool => Auth::user()?->hasRole('super_admin') ?? false)
                    ->navigationGroup('Sistem')
                    ->navigationLabel('Log Sistem')
                    ->navigationSort(90),
                FilamentShieldPlugin::make()
                    ->navigationLabel('Hak Akses')
                    ->navigationGroup('Manajemen Pengguna')
                    ->navigationBadge(fn(): string => (string) Role::count())
                    ->navigationBadgeTooltip('Total peran')
                    ->navigationBadgeColor('warning')
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
