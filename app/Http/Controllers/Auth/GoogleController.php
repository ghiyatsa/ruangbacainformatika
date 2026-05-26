<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthenticationRedirector;
use App\Services\GoogleIdTokenVerifier;
use App\Services\MemberRegistrationClaimService;
use App\Support\CampusEmail;
use App\Support\GoogleAccountData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GoogleController extends Controller
{
    public function __construct(
        protected AuthenticationRedirector $authenticationRedirector,
        protected CampusEmail $campusEmail,
        protected GoogleIdTokenVerifier $googleIdTokenVerifier,
        protected MemberRegistrationClaimService $memberRegistrationClaimService,
    ) {}

    public function redirectToGoogle(Request $request): Response
    {
        if (! $this->isGoogleLoginConfigured()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Konfigurasi login Google belum lengkap.',
            ]);

            return redirect()->route('home');
        }

        $linkToken = $request->string('link_token')->trim()->toString();

        if ($linkToken !== '') {
            $claim = $this->memberRegistrationClaimService->findByToken($linkToken);

            if ($claim === null || $claim->isExpired()) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Tautan penautan akun sudah tidak valid. Silakan daftar ulang di kiosk.',
                ]);

                return redirect()->route('home');
            }

            $this->memberRegistrationClaimService->clearAttemptError($linkToken);
            $request->session()->put('auth.google.link_token', $linkToken);
        }

        $redirectResponse = Socialite::driver('google')->redirect();

        if ($request->inertia()) {
            return Inertia::location($redirectResponse->getTargetUrl());
        }

        return $redirectResponse;
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        $linkToken = request()->session()->pull('auth.google.link_token');

        try {
            if (! $this->isGoogleLoginConfigured()) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Konfigurasi login Google belum lengkap.',
                ]);

                return redirect()->route('home');
            }

            $googleUser = Socialite::driver('google')->user();
            $googleAccount = $this->resolveGoogleAccount(
                $googleUser->getEmail(),
                is_string($linkToken) ? $linkToken : null,
            );

            if ($googleAccount instanceof RedirectResponse) {
                return $googleAccount;
            }

            return $this->authenticateGoogleIdentity(
                googleId: $googleUser->getId(),
                email: $googleAccount->email,
                name: $googleUser->getName(),
                linkToken: is_string($linkToken) ? $linkToken : null,
            );
        } catch (ValidationException $exception) {
            return $this->redirectToEntryWithError(
                collect($exception->errors())->flatten()->first() ?: 'Login dengan Google gagal.',
                is_string($linkToken) ? $linkToken : null,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->redirectToEntryWithError(
                'Login dengan Google gagal. Silakan coba lagi.',
                is_string($linkToken) ? $linkToken : null,
            );
        }
    }

    public function handleOneTap(Request $request): RedirectResponse
    {
        $linkToken = $request->string('link_token')->trim()->toString();

        try {
            if (! $this->isGoogleLoginConfigured()) {
                return $this->redirectToEntryWithError(
                    'Konfigurasi login Google belum lengkap.',
                    $linkToken !== '' ? $linkToken : null,
                );
            }

            $validated = $request->validate([
                'credential' => ['required', 'string', 'max:8192'],
                'link_token' => ['nullable', 'string', 'max:255'],
            ]);

            $identity = $this->googleIdTokenVerifier->verify($validated['credential']);
            $googleAccount = $this->resolveGoogleAccount(
                $identity['email'],
                $linkToken !== '' ? $linkToken : null,
            );

            if ($googleAccount instanceof RedirectResponse) {
                return $googleAccount;
            }

            return $this->authenticateGoogleIdentity(
                googleId: $identity['sub'],
                email: $googleAccount->email,
                name: $identity['name'],
                linkToken: $linkToken !== '' ? $linkToken : null,
            );
        } catch (ValidationException $exception) {
            return $this->redirectToEntryWithError(
                collect($exception->errors())->flatten()->first() ?: 'Google One Tap gagal diproses.',
                $linkToken !== '' ? $linkToken : null,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->redirectToEntryWithError(
                'Google One Tap gagal diproses. Silakan coba lagi.',
                $linkToken !== '' ? $linkToken : null,
            );
        }
    }

    protected function authenticateGoogleIdentity(
        string $googleId,
        string $email,
        ?string $name,
        ?string $linkToken = null,
    ): RedirectResponse {
        $existingUser = User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        $pendingLink = null;

        if ($linkToken !== null) {
            $pendingLink = $this->memberRegistrationClaimService->findByToken($linkToken);

            if (! $pendingLink) {
                throw ValidationException::withMessages([
                    'link' => 'Permintaan penautan akun tidak ditemukan atau sudah tidak berlaku.',
                ]);
            }

            if (! hash_equals(Str::lower($pendingLink->email), $email)) {
                throw ValidationException::withMessages([
                    'link' => 'Gunakan akun Google dengan email UNIMAL yang sama seperti data registrasi.',
                ]);
            }
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name ?: $existingUser?->name ?: Str::before($email, '@'),
                'google_id' => $googleId,
                'auth_provider' => 'google',
                'is_approved' => $existingUser?->is_approved ?? false,
            ],
        );

        if ($pendingLink !== null) {
            $this->memberRegistrationClaimService->consume($linkToken, $googleId, $user);
        }

        Auth::guard('web')->login($user);
        request()->session()->regenerate();

        return $this->authenticationRedirector->redirectResponse(request(), $user);
    }

    protected function resolveGoogleAccount(?string $rawEmail, ?string $linkToken = null): GoogleAccountData|RedirectResponse
    {
        if ($rawEmail === null) {
            return $this->redirectToEntryWithError('Akun Google Anda tidak memiliki email yang valid.', $linkToken);
        }

        $normalizedEmail = Str::lower(trim($rawEmail));

        if ($normalizedEmail === '') {
            return $this->redirectToEntryWithError('Akun Google Anda tidak memiliki email yang valid.', $linkToken);
        }

        if (! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->redirectToEntryWithError('Akun Google Anda tidak memiliki email yang valid.', $linkToken);
        }

        return new GoogleAccountData(
            email: $normalizedEmail,
            isApproved: $this->campusEmail->isTeknikInformatikaStudentEmail($normalizedEmail),
        );
    }

    protected function isGoogleLoginConfigured(): bool
    {
        return filled(Config::string('services.google.client_id'))
            && filled(Config::string('services.google.client_secret'))
            && filled(Config::string('services.google.redirect'));
    }

    protected function redirectToEntryWithError(string $message, ?string $linkToken = null): RedirectResponse
    {
        Inertia::flash('toast', [
            'type' => 'error',
            'message' => $message,
        ]);

        if ($linkToken !== null && $linkToken !== '') {
            $this->memberRegistrationClaimService->recordAttemptError($linkToken, $message);
        }

        return redirect()->route('home');
    }
}
