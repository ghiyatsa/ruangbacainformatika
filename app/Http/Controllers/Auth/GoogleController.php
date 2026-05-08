<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthenticationRedirector;
use App\Support\CampusEmail;
use App\Support\GoogleAccountData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleController extends Controller
{
    public function __construct(
        protected AuthenticationRedirector $authenticationRedirector,
        protected CampusEmail $campusEmail,
    ) {}

    public function redirectToGoogle(): SymfonyRedirectResponse|RedirectResponse
    {
        if (! $this->isGoogleLoginConfigured()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Konfigurasi login Google belum lengkap.',
            ]);

            return redirect()->route('register.whatsapp');
        }

        return Socialite::driver('google')
            ->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            if (! $this->isGoogleLoginConfigured()) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Konfigurasi login Google belum lengkap.',
                ]);

                return redirect()->route('login');
            }

            $googleUser = Socialite::driver('google')->user();
            $googleAccount = $this->resolveGoogleAccount($googleUser->getEmail());

            if ($googleAccount instanceof RedirectResponse) {
                return $googleAccount;
            }

            $existingUser = User::query()->where('email', $googleAccount->email)->first();

            $user = User::updateOrCreate(
                ['email' => $googleAccount->email],
                [
                    'name' => $googleUser->getName() ?: $existingUser?->name ?: Str::before($googleAccount->email, '@'),
                    'auth_provider' => 'google',
                    'is_approved' => $googleAccount->isApproved,
                    'password' => $existingUser?->password ?? Hash::make(Str::random(16)),
                ],
            );

            if (! $user->hasVerifiedEmail()) {
                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }

            if (Role::query()->where('name', 'member')->exists() && $user->shouldReceiveMemberRole()) {
                $user->assignRole('member');
            }

            Auth::guard('web')->login($user);
            request()->session()->put('auth.password_confirmed_at', time());
            request()->session()->regenerate();

            return $this->authenticationRedirector->redirectResponse(request(), $user);
        } catch (Throwable $exception) {
            report($exception);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Login dengan Google gagal. Silakan coba lagi.',
            ]);

            return redirect()->route('login');
        }
    }

    protected function resolveGoogleAccount(?string $rawEmail): GoogleAccountData|RedirectResponse
    {
        if ($rawEmail === null) {
            return $this->redirectToLoginWithError('Akun Google Anda tidak memiliki email yang valid.');
        }

        $normalizedEmail = Str::lower(trim($rawEmail));

        if ($normalizedEmail === '') {
            return $this->redirectToLoginWithError('Akun Google Anda tidak memiliki email yang valid.');
        }

        if ($message = $this->campusEmail->validationMessage($normalizedEmail)) {
            return $this->redirectToLoginWithError($message);
        }

        return new GoogleAccountData(
            email: $normalizedEmail,
            isApproved: $this->campusEmail->isMahasiswaEmail($normalizedEmail),
        );
    }

    protected function isGoogleLoginConfigured(): bool
    {
        return filled(Config::string('services.google.client_id'))
            && filled(Config::string('services.google.client_secret'))
            && filled(Config::string('services.google.redirect'));
    }

    protected function redirectToLoginWithError(string $message): RedirectResponse
    {
        Inertia::flash('toast', [
            'type' => 'error',
            'message' => $message,
        ]);

        return redirect()->route('login');
    }
}
