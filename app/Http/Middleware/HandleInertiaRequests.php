<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Auth\AuthenticationRedirector;
use App\Services\LoanDraftService;
use App\Support\LoginViewData;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        protected LoanDraftService $loanDraftService,
        protected SiteSettings $siteSettings,
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
        $user = $request->user();
        $siteData = $this->siteSettings->shared();

        return [
            ...parent::share($request),
            ...$siteData,
            'auth' => [
                'user' => $this->serializeUser($user),
                'canAccessAdminPanel' => $user?->canAccessAdminPanel() ?? false,
                'canBorrowBooks' => $user?->canBorrowBooks() ?? false,
                'hasVerifiedWhatsApp' => $user?->hasVerifiedWhatsApp() ?? false,
                'requiresWhatsAppVerification' => $user?->requiresWhatsAppVerification() ?? false,
                'borrowingAccessMessage' => $user !== null && ! $user->canBorrowBooks()
                    ? ($user->requiresWhatsAppVerification()
                        ? 'Verifikasi WhatsApp diperlukan sebelum layanan anggota dapat digunakan.'
                        : ($user->requiresManualApproval()
                            ? 'Akun kampus Anda sedang menunggu persetujuan admin.'
                            : 'Layanan peminjaman tersedia untuk anggota yang telah disetujui.'))
                    : null,
                'homeUrl' => $user === null
                    ? route('home', absolute: false)
                    : app(AuthenticationRedirector::class)->pathFor($user),
            ],
            'notifications' => fn (): array => [
                'unreadCount' => $user?->unreadNotifications()->count() ?? 0,
            ],
            'googleAuth' => [
                'clientId' => filled(config('services.google.client_id'))
                    ? config('services.google.client_id')
                    : null,
                'loginUrl' => route('auth.google', absolute: false),
                'oneTapUrl' => route('auth.google.one-tap', absolute: false),
                'enabled' => app(LoginViewData::class)->canLoginWithGoogle(),
            ],
            'loanRequestCart' => fn (): ?array => $user?->canBorrowBooks()
                ? $this->loanDraftService->summary($user)
                : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'status' => $session?->get('status'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function serializeUser(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatarUrl(),
            'whatsapp' => $user->whatsapp,
            'whatsapp_verified_at' => $user->whatsapp_verified_at?->toIso8601String(),
            'address' => $user->address,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }
}
