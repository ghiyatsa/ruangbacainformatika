<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\Auth\AuthenticationRedirector;
use App\Services\MemberRegistrationClaimService;
use App\Support\CampusEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticateGoogleIdentity
{
    public function __construct(
        protected AuthenticationRedirector $authenticationRedirector,
        protected CampusEmail $campusEmail,
        protected MemberRegistrationClaimService $memberRegistrationClaimService,
    ) {}

    public function execute(
        Request $request,
        string $googleId,
        string $email,
        ?string $name,
        ?string $avatarUrl = null,
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
                'avatar_url' => filter_var($avatarUrl, FILTER_VALIDATE_URL) ? $avatarUrl : $existingUser?->avatarUrl(),
                'auth_provider' => 'google',
                'is_approved' => $existingUser?->is_approved || $this->campusEmail->shouldAutoApprove($email),
            ],
        );

        if ($pendingLink !== null) {
            $this->memberRegistrationClaimService->consume($linkToken, $googleId, $user);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return $this->authenticationRedirector->redirectResponse($request, $user);
    }
}
