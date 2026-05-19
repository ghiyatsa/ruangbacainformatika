<?php

namespace App\Http\Middleware;

use App\Services\Auth\AuthenticationRedirector;
use App\Services\LoanDraftService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        protected LoanDraftService $loanDraftService,
    ) {}

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $session = $request->hasSession() ? $request->session() : null;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'site' => [
                'url' => rtrim((string) config('app.url'), '/'),
                'description' => 'Perpustakaan digital resmi Program Studi Teknik Informatika Universitas Malikussaleh untuk mendukung pembelajaran, riset, dan akses koleksi akademik.',
                'department' => 'Program Studi Teknik Informatika Universitas Malikussaleh',
                'contactEmail' => 'informatika@unimal.ac.id',
                'address' => 'Jl. Cot Tengku Nie, Reuleut, Aceh Utara 24355',
                'ogImage' => asset('images/og-image.png'),
            ],
            'auth' => [
                'user' => $request->user(),
                'canAccessAdminPanel' => $request->user()?->canAccessAdminPanel() ?? false,
                'homeUrl' => $request->user() === null
                    ? route('home', absolute: false)
                    : app(AuthenticationRedirector::class)->pathFor($request->user()),
            ],
            'loanRequestCart' => $request->user()
                ? $this->loanDraftService->summary($request->user())
                : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'status' => $session?->get('status'),
            'verification_resend_available_at' => $session?->get('verification_resend_available_at'),
            'password_reset_resend_available_at' => $session?->get('password_reset_resend_available_at'),
        ];
    }
}
