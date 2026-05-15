<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthenticationRedirector
{
    /**
     * @var list<string>
     */
    protected const DISALLOWED_INTENDED_PATHS = [
        '/login',
        '/register',
        '/register/profile',
        '/auth/google',
        '/auth/google/callback',
    ];

    public function destinationFor(User $user, ?string $intended = null): string
    {
        $defaultPath = $this->defaultPathFor($user);

        if ($this->mustUseDefaultPath($user) || blank($intended)) {
            return $defaultPath;
        }

        $intendedPath = parse_url($intended, PHP_URL_PATH);

        if (! is_string($intendedPath) || $this->isDisallowedIntendedPath($user, $intendedPath)) {
            return $defaultPath;
        }

        return $intended;
    }

    public function pathFor(User $user): string
    {
        return $this->defaultPathFor($user);
    }

    public function redirectResponse(Request $request, ?User $user = null): RedirectResponse
    {
        $authenticatedUser = $user ?? $request->user();

        abort_unless($authenticatedUser instanceof User, 500, 'Authenticated user is required.');

        return redirect()->to(
            $this->destinationFor($authenticatedUser, $request->session()->pull('url.intended')),
        );
    }

    protected function defaultPathFor(User $user): string
    {
        if (! $user->hasVerifiedEmail()) {
            return route('verification.notice', absolute: false);
        }

        if ($this->requiresProfileCompletion($user)) {
            return route('register.profile', absolute: false);
        }

        if ($user->canAccessAdminPanel()) {
            return route('filament.admin.pages.dashboard', absolute: false);
        }

        return route('home', absolute: false);
    }

    public function requiresProfileCompletion(User $user): bool
    {
        return ! $user->hasRequiredProfileDetails();
    }

    protected function mustUseDefaultPath(User $user): bool
    {
        return ! $user->hasVerifiedEmail() || $this->requiresProfileCompletion($user);
    }

    protected function isDisallowedIntendedPath(User $user, string $intendedPath): bool
    {
        if (Str::startsWith($intendedPath, '/admin') && ! $user->canAccessAdminPanel()) {
            return true;
        }

        return in_array($intendedPath, self::DISALLOWED_INTENDED_PATHS, true);
    }
}
