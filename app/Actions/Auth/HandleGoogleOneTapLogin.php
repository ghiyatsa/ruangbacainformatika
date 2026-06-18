<?php

namespace App\Actions\Auth;

use App\Http\Requests\Auth\GoogleOneTapRequest;
use App\Services\Auth\GoogleLoginConfiguration;
use App\Services\GoogleIdTokenVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class HandleGoogleOneTapLogin
{
    public function __construct(
        protected AuthenticateGoogleIdentity $authenticateGoogleIdentity,
        protected GoogleIdTokenVerifier $googleIdTokenVerifier,
        protected GoogleLoginConfiguration $googleLoginConfiguration,
        protected ResolveGoogleAccount $resolveGoogleAccount,
    ) {}

    public function execute(GoogleOneTapRequest $request): RedirectResponse
    {
        if (! $this->googleLoginConfiguration->isConfigured()) {
            throw ValidationException::withMessages([
                'credential' => 'Konfigurasi login Google belum lengkap.',
            ]);
        }

        $identity = $this->googleIdTokenVerifier->verify($request->validatedCredential());
        $email = $this->resolveGoogleAccount->execute($identity['email']);

        return $this->authenticateGoogleIdentity->execute(
            request: $request,
            googleId: $identity['sub'],
            email: $email,
            name: $identity['name'],
            avatarUrl: $identity['avatar'] ?? null,
            linkToken: $request->validatedLinkToken(),
        );
    }
}
