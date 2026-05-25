<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    /**
     * Show the user's security settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/security', [
            'sessions' => $this->getSessions($request),
        ]);
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
}
