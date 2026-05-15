<?php

namespace App\Providers;

use App\Models\Skripsi;
use App\Observers\SkripsiObserver;
use App\Repositories\SettingRepository;
use App\Services\SimilarityApiService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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

        Skripsi::observe(SkripsiObserver::class);
    }

    protected function configureTurnstile(): void
    {
        try {
            // Only load settings if table exists to avoid errors during migrations
            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            $settings = app(SettingRepository::class);
            $enabled = $settings->get('integration', 'turnstile_enabled', false);

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

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Gate::before(function ($user, string $ability): ?bool {
            return method_exists($user, 'hasRole') && $user->hasRole('super_admin')
                ? true
                : null;
        });

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }
}
