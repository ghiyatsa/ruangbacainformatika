<?php

namespace App\Providers;

use App\Listeners\DispatchSimilaritySyncAfterSkripsiImport;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\InternshipReport;
use App\Models\Post;
use App\Models\Publisher;
use App\Models\Skripsi;
use App\Models\Thesis;
use App\Observers\CatalogActivityObserver;
use App\Observers\PostObserver;
use App\Observers\SkripsiObserver;
use App\Repositories\SettingRepository;
use App\Services\ActivityLogService;
use App\Services\KioskPinManager;
use App\Services\SimilarityApiService;
use App\Support\AppTimezone;
use App\Support\SiteSettings;
use Carbon\CarbonImmutable;
use Filament\Actions\Imports\Events\ImportCompleted;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Inertia\ExceptionResponse;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(ActivityLogService::class);
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
        $this->configureKioskRateLimiters();
        $this->composeRootView();

        Author::observe(CatalogActivityObserver::class);
        Book::observe(CatalogActivityObserver::class);
        Category::observe(CatalogActivityObserver::class);
        InternshipReport::observe(CatalogActivityObserver::class);
        Post::observe(PostObserver::class);
        Publisher::observe(CatalogActivityObserver::class);
        Skripsi::observe(CatalogActivityObserver::class);
        Skripsi::observe(SkripsiObserver::class);
        Thesis::observe(CatalogActivityObserver::class);

        Event::listen(
            ImportCompleted::class,
            DispatchSimilaritySyncAfterSkripsiImport::class,
        );
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

    protected function configureKioskRateLimiters(): void
    {
        RateLimiter::for('kiosk-pin', function (Request $request): Limit {
            return Limit::perMinute(8)
                ->by($this->kioskThrottleKey($request, 'pin'))
                ->response(fn (Request $request, array $headers) => $this->kioskThrottleResponse(
                    $request,
                    $headers,
                    'Terlalu banyak percobaan PIN. Coba lagi sebentar.',
                ));
        });

        RateLimiter::for('kiosk-book-search', function (Request $request): Limit {
            return Limit::perMinute(180)
                ->by($this->kioskThrottleKey($request, 'book-search'))
                ->response(fn (Request $request, array $headers) => $this->kioskThrottleResponse(
                    $request,
                    $headers,
                    'Pencarian buku sedang dibatasi sebentar. Coba lagi sesaat.',
                ));
        });

        RateLimiter::for('kiosk-member-lookup', function (Request $request): Limit {
            return Limit::perMinute(120)
                ->by($this->kioskThrottleKey($request, 'member-lookup'))
                ->response(fn (Request $request, array $headers) => $this->kioskThrottleResponse(
                    $request,
                    $headers,
                    'Pencarian anggota sedang dibatasi sebentar. Coba lagi sesaat.',
                ));
        });

        RateLimiter::for('kiosk-member-status', function (Request $request): Limit {
            return Limit::perMinute(180)
                ->by($this->kioskThrottleKey($request, 'member-status'))
                ->response(fn (Request $request, array $headers) => $this->kioskThrottleResponse(
                    $request,
                    $headers,
                    'Status penautan sedang diperiksa terlalu sering. Coba lagi sebentar.',
                ));
        });

        RateLimiter::for('kiosk-submit', function (Request $request): Limit {
            return Limit::perMinute(30)
                ->by($this->kioskThrottleKey($request, 'submit'))
                ->response(fn (Request $request, array $headers) => $this->kioskThrottleResponse(
                    $request,
                    $headers,
                    'Permintaan layanan mandiri sedang dibatasi sebentar. Coba lagi sesaat.',
                ));
        });

        RateLimiter::for('kiosk-consume', function (Request $request): Limit {
            return Limit::perMinute(20)
                ->by($this->kioskThrottleKey($request, 'consume'))
                ->response(fn (Request $request, array $headers) => $this->kioskThrottleResponse(
                    $request,
                    $headers,
                    'Pemrosesan QR sedang dibatasi sebentar. Coba lagi sesaat.',
                ));
        });
    }

    protected function kioskThrottleKey(Request $request, string $bucket): string
    {
        $deviceToken = (string) ($request->cookie(KioskPinManager::COOKIE_DEVICE_TOKEN_KEY) ?? '');
        $sessionId = $request->hasSession() ? (string) $request->session()->getId() : 'no-session';
        $deviceFingerprint = $deviceToken !== '' ? $deviceToken : $sessionId;

        return implode('|', [
            'kiosk',
            $bucket,
            (string) $request->ip(),
            $deviceFingerprint,
        ]);
    }

    protected function kioskThrottleResponse(Request $request, array $headers, string $message): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
            ], 429, $headers);
        }

        return response($message, 429, $headers);
    }

    protected function configureTurnstile(): void
    {
        try {
            // Only load settings if not running in console (avoiding queries during migrations and tests boot)
            if (app()->runningInConsole()) {
                return;
            }

            $enabled = app(SettingRepository::class)->get('integration', 'turnstile_enabled', false);
            $hasCredentials = filled(config('services.turnstile.key'))
                && filled(config('services.turnstile.secret'));

            config([
                'services.turnstile.enabled' => filter_var($enabled, FILTER_VALIDATE_BOOLEAN) && $hasCredentials,
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
                    ->render('error/index', [
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
