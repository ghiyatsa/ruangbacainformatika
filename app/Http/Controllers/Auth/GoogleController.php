<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateGoogleIdentity;
use App\Actions\Auth\HandleGoogleOneTapLogin;
use App\Actions\Auth\ResolveGoogleAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleOneTapRequest;
use App\Services\Auth\GoogleLoginConfiguration;
use App\Services\MemberRegistrationClaimService;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GoogleController extends Controller
{
    public function __construct(
        protected AuthenticateGoogleIdentity $authenticateGoogleIdentity,
        protected GoogleLoginConfiguration $googleLoginConfiguration,
        protected HandleGoogleOneTapLogin $handleGoogleOneTapLogin,
        protected MemberRegistrationClaimService $memberRegistrationClaimService,
        protected ResolveGoogleAccount $resolveGoogleAccount,
    ) {}

    public function setPopupSession(Request $request): Response
    {
        $request->session()->put('auth.google.popup', true);

        return response()->json(['success' => true]);
    }

    public function redirectToGoogle(Request $request): Response
    {
        if (! $this->googleLoginConfiguration->isConfigured()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Konfigurasi login Google belum lengkap.',
            ]);

            return redirect()->route('home');
        }

        $linkToken = $request->string('link_token')->trim()->toString();

        if ($request->has('popup')) {
            $request->session()->put('auth.google.popup', true);
        }

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

        $redirectResponse = $this->googleProvider($request)->redirect();

        if ($request->inertia()) {
            return Inertia::location($redirectResponse->getTargetUrl());
        }

        return $redirectResponse;
    }

    public function handleGoogleCallback(): Response
    {
        $linkToken = request()->session()->pull('auth.google.link_token');
        $isPopup = request()->session()->pull('auth.google.popup', false);

        try {
            if (! $this->googleLoginConfiguration->isConfigured()) {
                if ($isPopup) {
                    $nonce = Vite::cspNonce();

                    return response('<script nonce="'.$nonce.'">window.opener.postMessage({ type: "GOOGLE_AUTH_ERROR", message: "Konfigurasi login Google belum lengkap." }, window.location.origin); window.close();</script>');
                }
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Konfigurasi login Google belum lengkap.',
                ]);

                return redirect()->route('home');
            }

            $googleUser = $this->googleProvider(request())->user();
            $email = $this->resolveGoogleAccount->execute($googleUser->getEmail());

            $redirectResponse = $this->authenticateGoogleIdentity->execute(
                request: request(),
                googleId: $googleUser->getId(),
                email: $email,
                name: $googleUser->getName(),
                avatarUrl: $googleUser->getAvatar(),
                linkToken: is_string($linkToken) ? $linkToken : null,
            );

            if ($isPopup) {
                $nonce = Vite::cspNonce();
                $targetUrl = $redirectResponse->getTargetUrl();

                return response('<script nonce="'.$nonce.'">window.opener.postMessage({ type: "GOOGLE_AUTH_SUCCESS", url: "'.addslashes($targetUrl).'" }, window.location.origin); window.close();</script>');
            }

            return $redirectResponse;
        } catch (ValidationException $exception) {
            $errorMessage = collect($exception->errors())->flatten()->first() ?: 'Login dengan Google gagal.';
            if ($isPopup) {
                $nonce = Vite::cspNonce();

                return response('<script nonce="'.$nonce.'">window.opener.postMessage({ type: "GOOGLE_AUTH_ERROR", message: "'.addslashes($errorMessage).'" }, window.location.origin); window.close();</script>');
            }

            return $this->redirectToEntryWithError(
                $errorMessage,
                is_string($linkToken) ? $linkToken : null,
            );
        } catch (Throwable $exception) {
            report($exception);

            if ($isPopup) {
                $nonce = Vite::cspNonce();

                return response('<script nonce="'.$nonce.'">window.opener.postMessage({ type: "GOOGLE_AUTH_ERROR", message: "Login dengan Google gagal. Silakan coba lagi." }, window.location.origin); window.close();</script>');
            }

            return $this->redirectToEntryWithError(
                'Login dengan Google gagal. Silakan coba lagi.',
                is_string($linkToken) ? $linkToken : null,
            );
        }
    }

    public function handleOneTap(GoogleOneTapRequest $request): Response
    {
        $linkToken = $request->validatedLinkToken();

        try {
            return $this->toOneTapResponse(
                $request,
                $this->handleGoogleOneTapLogin->execute($request),
            );
        } catch (ValidationException $exception) {
            return $this->redirectToEntryWithError(
                collect($exception->errors())->flatten()->first() ?: 'Google One Tap gagal diproses.',
                $linkToken,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->redirectToEntryWithError(
                'Google One Tap gagal diproses. Silakan coba lagi.',
                $linkToken,
            );
        }
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

    protected function toOneTapResponse(Request $request, RedirectResponse $response): Response
    {
        if (! $request->inertia()) {
            return $response;
        }

        $targetPath = parse_url($response->getTargetUrl(), PHP_URL_PATH);

        if (is_string($targetPath) && Str::startsWith($targetPath, '/admin')) {
            return Inertia::location($response->getTargetUrl());
        }

        return $response;
    }

    protected function googleProvider(Request $request): Provider
    {
        return Socialite::driver('google')
            ->setRequest($request)
            ->setHttpClient(new Client([
                'proxy' => null,
            ]));
    }
}
