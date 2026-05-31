<?php

namespace App\Providers;

use App\Models\Skripsi;
use App\Observers\SkripsiObserver;
use App\Repositories\SettingRepository;
use App\Services\SimilarityApiService;
use App\Support\AppTimezone;
use App\Support\SiteSettings;
use Carbon\CarbonImmutable;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Inertia\ExceptionResponse;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(SimilarityApiService::class);
        $this->app->scoped(SiteSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        $this->configureDefaults();
        $this->configureInertiaExceptionHandling();
        $this->configureTurnstile();
        $this->configureWhatsAppRateLimiter();
        $this->configureContactRateLimiter();
        $this->composeRootView();

        Skripsi::observe(SkripsiObserver::class);
    }

    protected function configureWhatsAppRateLimiter(): void
    {
        RateLimiter::for('whatsapp-notifications', function (object $job): Limit {
            $intervalSeconds = max((int) config('services.fonnte.send_interval_seconds', 15), 1);
            $maxPerMinute = max((int) floor(60 / $intervalSeconds), 1);

            return Limit::perMinute($maxPerMinute)->by('global-whatsapp-notifications');
        });
    }

    protected function configureContactRateLimiter(): void
    {
        RateLimiter::for('contact-messages', function (Request $request): Limit {
            return Limit::perMinute(5)
                ->by((string) ($request->user()?->id ?? $request->ip()))
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Terlalu banyak percobaan mengirim pesan. Coba lagi sebentar.',
                    ], 429, $headers);
                });
        });
    }

    protected function configureTurnstile(): void
    {
        try {
            // Only load settings if not running in console (avoiding queries during migrations and tests boot)
            if (app()->runningInConsole()) {
                return;
            }

            $enabled = cache()->remember('settings.integration.turnstile_enabled', now()->addMinutes(5), function (): mixed {
                return app(SettingRepository::class)->get('integration', 'turnstile_enabled', false);
            });

            config([
                'services.turnstile.enabled' => filter_var($enabled, FILTER_VALIDATE_BOOLEAN),
            ]);
        } catch (\Exception) {
            // Silence errors during initial setup or if table doesn't exist
        }
    }

    protected function configureInertiaExceptionHandling(): void
    {
        Inertia::handleExceptionsUsing(function (ExceptionResponse $response) {
            $statusCode = $response->statusCode();

            if (app()->environment('local') && in_array($statusCode, [500, 503], true)) {
                return null;
            }

            if (in_array($statusCode, [403, 404, 419, 429, 500, 503], true)) {
                return $response
                    ->render('error-page', [
                        'status' => $statusCode,
                    ])
                    ->withSharedData();
            }

            return null;
        });
    }

    protected function composeRootView(): void
    {
        View::composer('app', function ($view): void {
            $view->with(app(SiteSettings::class)->rootViewData());
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);
        FilamentTimezone::set(AppTimezone::displayTimezone());

        Gate::before(function ($user, string $ability): ?bool {
            return method_exists($user, 'hasRole') && $user->hasRole('super_admin')
                ? true
                : null;
        });

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(function (): Password {
            $rule = Password::min(8)
                ->letters()
                ->numbers();

            if (app()->isProduction()) {
                return $rule->uncompromised();
            }

            return $rule;
        });
    }
}
