<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class SecurityController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return Features::canManageTwoFactorAuthentication()
            && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
                ? [new Middleware('password.confirm', only: ['edit'])]
                : [];
    }

    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request): Response
    {
        $user = $request->user();

        $props = [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'sessions' => $this->getSessions($request),
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $props['twoFactorEnabled'] = ! is_null($user->two_factor_secret);
            $props['twoFactorConfirmed'] = ! is_null($user->two_factor_confirmed_at);
            $props['requiresConfirmation'] = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        return Inertia::render('settings/security', $props);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Password updated.')]);

        return back();
    }

    /**
     * Log out from other browser sessions.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (! Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        Auth::logoutOtherDevices($request->password);

        $this->deleteOtherSessionRecords($request);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Other browser sessions logged out.')]);

        return back();
    }

    /**
     * Get the current sessions.
     */
    protected function getSessions(Request $request): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        return collect(DB::table('sessions')
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->orderBy('last_activity', 'desc')
            ->get())->map(function ($session) use ($request) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'is_current_device' => $session->id === $request->session()->getId(),
                    'agent' => $this->parseAgent($session->user_agent),
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];
            })->toArray();
    }

    /**
     * Simple User Agent parser.
     */
    protected function parseAgent(string $userAgent): array
    {
        $userAgent = strtolower($userAgent);

        $platform = 'Unknown';
        if (str_contains($userAgent, 'windows')) {
            $platform = 'Windows';
        } elseif (str_contains($userAgent, 'macintosh') || str_contains($userAgent, 'mac os x')) {
            $platform = 'macOS';
        } elseif (str_contains($userAgent, 'linux')) {
            $platform = 'Linux';
        } elseif (str_contains($userAgent, 'android')) {
            $platform = 'Android';
        } elseif (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            $platform = 'iOS';
        }

        $browser = 'Unknown';
        if (str_contains($userAgent, 'chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'safari') && ! str_contains($userAgent, 'chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'edge')) {
            $browser = 'Edge';
        }

        return [
            'is_desktop' => ! preg_match('/mobile|android|iphone|ipad|phone/i', $userAgent),
            'platform' => $platform,
            'browser' => $browser,
        ];
    }

    /**
     * Delete the other browser session records from storage.
     */
    protected function deleteOtherSessionRecords(Request $request): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::table('sessions')
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('id', '!=', $request->session()->getId())
            ->delete();
    }
}
