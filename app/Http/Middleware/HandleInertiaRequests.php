<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Repositories\SettingRepository;
use App\Services\Auth\AuthenticationRedirector;
use App\Services\LoanDraftService;
use App\Support\LoginViewData;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        protected LoanDraftService $loanDraftService,
        protected SettingRepository $settingRepository,
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
        $generalSettings = $this->settingRepository->sectionValues('general', [
            'hero_notice_enabled' => '0',
            'hero_notice_text' => '',
            'hero_notice_url' => '',
            'hero_notice_link_label' => '',
            'hero_notice_tone' => 'info',
        ]);
        $heroNoticeText = trim((string) ($generalSettings['hero_notice_text'] ?? ''));
        $heroNoticeUrl = trim((string) ($generalSettings['hero_notice_url'] ?? ''));
        $heroNoticeLinkLabel = trim((string) ($generalSettings['hero_notice_link_label'] ?? ''));
        $heroNoticeTone = in_array($generalSettings['hero_notice_tone'] ?? null, ['info', 'warning', 'success'], true)
            ? $generalSettings['hero_notice_tone']
            : 'info';

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
                'notice' => [
                    'isActive' => ($generalSettings['hero_notice_enabled'] ?? '0') === '1'
                        && $heroNoticeText !== '',
                    'text' => $heroNoticeText,
                    'url' => $heroNoticeUrl !== '' ? $heroNoticeUrl : null,
                    'linkLabel' => $heroNoticeLinkLabel !== '' ? $heroNoticeLinkLabel : null,
                    'tone' => $heroNoticeTone,
                ],
            ],
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
            'whatsapp' => $user->whatsapp,
            'whatsapp_verified_at' => $user->whatsapp_verified_at?->toIso8601String(),
            'address' => $user->address,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }
}
