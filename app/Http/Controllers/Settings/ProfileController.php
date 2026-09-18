<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendWhatsAppOtpRequest;
use App\Http\Requests\Auth\VerifyWhatsAppOtpRequest;
use App\Http\Requests\Settings\AccountDeletionRequest;
use App\Http\Requests\Settings\ProfileOnboardingRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use App\Services\AccountDeletionService;
use App\Services\Auth\AuthenticationRedirector;
use App\Services\WhatsAppOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        protected AuthenticationRedirector $authenticationRedirector,
        protected WhatsAppOtpService $whatsAppOtpService,
    ) {}

    /**
     * Verifikasi WhatsApp dan data profil hanya relevan untuk akun kampus.
     * Akun non-kampus tidak dapat meminjam buku sehingga tidak perlu
     * mengirim OTP maupun menyimpan nomor/alamat.
     */
    protected function ensureCampusAccount(User $user): void
    {
        if (! $user->usesCampusEmail()) {
            throw ValidationException::withMessages([
                'whatsapp' => 'Verifikasi WhatsApp hanya tersedia untuk akun dengan email kampus.',
            ]);
        }
    }

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $deletion = app(AccountDeletionService::class);

        return Inertia::render('settings/profile', [
            'verification' => $user->usesCampusEmail()
                ? $this->whatsAppOtpService->status($user)
                : null,
            'canManageCampusContact' => $user->usesCampusEmail(),
            'accountDeletion' => [
                'blockingReason' => $deletion->blockingReason($user),
                'confirmationPhrase' => AccountDeletionService::CONFIRMATION_PHRASE,
                'gracePeriodDays' => AccountDeletionService::GRACE_PERIOD_DAYS,
            ],
            'meta' => ['robots' => 'noindex, nofollow'],
        ]);
    }

    public function initiateWhatsAppChange(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->ensureCampusAccount($user);

        $request->session()->put('allow_whatsapp_change', true);

        return to_route('register.whatsapp');
    }

    public function sendWhatsAppOtp(SendWhatsAppOtpRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->ensureCampusAccount($user);

        $targetPhone = $request->filled('whatsapp')
            ? (string) $request->validated('whatsapp')
            : $user->whatsapp;

        if ($user->hasVerifiedWhatsApp() && $user->whatsapp === $targetPhone) {
            throw ValidationException::withMessages([
                'whatsapp' => 'Nomor WhatsApp baru harus berbeda dengan nomor saat ini.',
            ]);
        }

        $request->session()->put('allow_whatsapp_change', true);

        try {
            $this->whatsAppOtpService->dispatch($user, $targetPhone);
        } catch (\RuntimeException $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'otp' => 'Kode belum dapat dikirim. Coba lagi beberapa saat lagi.',
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kode OTP berhasil dikirim ke WhatsApp.',
        ]);

        return back();
    }

    public function verifyWhatsAppOtp(VerifyWhatsAppOtpRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->ensureCampusAccount($user);

        $this->whatsAppOtpService->verify(
            $user,
            (string) $request->validated('code'),
        );

        $request->session()->forget('allow_whatsapp_change');
        $request->session()->forget('whatsapp_verification_skipped');

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Nomor WhatsApp berhasil diverifikasi.',
        ]);

        return back();
    }

    public function complete(Request $request): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User) {
            return to_route('settings.profile.edit');
        }

        if (! $this->authenticationRedirector->requiresProfileCompletion($user)) {
            return to_route('home');
        }

        return Inertia::render('auth/register-profile', [
            'verification' => $this->whatsAppOtpService->status($user),
            'meta' => ['robots' => 'noindex, nofollow'],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $originalWhatsapp = $user->whatsapp;
        $payload = $request->validated();

        // Akun non-kampus tidak memakai WhatsApp maupun alamat domisili,
        // jadi kedua field itu diabaikan walau dikirim dari klien.
        if (! $user->usesCampusEmail()) {
            unset($payload['whatsapp'], $payload['address']);
        }

        $user->fill($payload);
        $user->save();

        if ($user->hasRequiredProfileDetails() && ! $user->hasCompletedProfile()) {
            $user->markProfileAsCompleted();
        }

        if ($originalWhatsapp !== $user->whatsapp && $user->requiresWhatsAppVerification()) {
            $request->session()->put('allow_whatsapp_change', true);

            Inertia::flash('toast', [
                'type' => 'info',
                'message' => 'Nomor WhatsApp diperbarui. Verifikasi ulang diperlukan.',
            ]);

            return to_route('register.whatsapp');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('settings.profile.edit');
    }

    public function storeOnboarding(ProfileOnboardingRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->authenticationRedirector->requiresProfileCompletion($user)) {
            return to_route('home');
        }

        if ($user->hasRequiredProfileDetails() && ! $user->requiresWhatsAppVerification()) {
            return to_route('settings.profile.edit');
        }

        $user->forceFill([
            'name' => $request->validated('name'),
            'whatsapp' => $request->validated('whatsapp'),
            'address' => $request->validated('address'),
        ]);
        $user->markProfileAsCompleted();

        $request->session()->forget('profile_completion_skipped');

        if ($request->filled('code')) {
            try {
                $this->whatsAppOtpService->verify(
                    $user,
                    (string) $request->validated('code'),
                );

                $request->session()->forget('whatsapp_verification_skipped');
            } catch (ValidationException $exception) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => $exception->errors()['code'][0] ?? 'Kode verifikasi tidak valid.',
                ]);

                return to_route('register.profile');
            }
        }

        if ($user->requiresWhatsAppVerification()) {
            return to_route('register.whatsapp');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Onboarding selesai.')]);

        return to_route('home');
    }

    public function skipOnboarding(Request $request): RedirectResponse
    {
        $request->session()->put('profile_completion_skipped', true);

        /** @var User $user */
        $user = $request->user();

        return redirect()->to($this->authenticationRedirector->destinationFor($user));
    }

    /**
     * Hapus akun atas permintaan pemiliknya.
     *
     * Data pribadi dianonimkan dan akun ditandai terhapus. Riwayat peminjaman
     * tidak disentuh sehingga statistik sirkulasi tetap utuh. Sesi diakhiri
     * agar pengguna langsung keluar.
     */
    public function destroyAccount(AccountDeletionRequest $request, AccountDeletionService $deletion): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $deletion->request($user, $request->validated('reason'));

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/')->with(
            'status',
            'Akun Anda telah dihapus. Data pribadi sudah dihapus dan akun tidak dapat diakses lagi.'
        );
    }
}
