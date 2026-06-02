<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Content-Security-Policy', $this->buildContentSecurityPolicy($request));
        $response->headers->set(
            'Permissions-Policy',
            $this->buildPermissionsPolicy($request),
        );

        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
            );
        }

        return $response;
    }

    protected function buildContentSecurityPolicy(Request $request): string
    {
        $nonce = Vite::cspNonce();
        $scriptSources = [
            "'self'",
            "'nonce-{$nonce}'",
            'https://accounts.google.com/gsi/client',
            'https://accounts.google.com',
            'https://challenges.cloudflare.com',
        ];
        $styleSources = [
            "'self'",
            "'unsafe-inline'",
            'https://fonts.bunny.net',
        ];
        $connectSources = [
            "'self'",
            'https://accounts.google.com',
            'https://www.googleapis.com',
            'https://challenges.cloudflare.com',
        ];

        if (app()->isLocal()) {
            array_push($scriptSources, 'http://127.0.0.1:5173', 'http://localhost:5173');
            array_push($styleSources, 'http://127.0.0.1:5173', 'http://localhost:5173');
            array_push($connectSources, 'http://127.0.0.1:5173', 'http://localhost:5173', 'ws://127.0.0.1:5173', 'ws://localhost:5173');
        }

        $fontSources = ["'self'", 'data:', 'https://fonts.bunny.net'];

        if (app()->isLocal()) {
            array_push($fontSources, 'http://127.0.0.1:5173', 'http://localhost:5173');
        }

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self' https://accounts.google.com",
            "img-src 'self' data: https:",
            'font-src '.implode(' ', $fontSources),
            'style-src '.implode(' ', $styleSources),
            'script-src '.implode(' ', $scriptSources),
            'connect-src '.implode(' ', $connectSources),
            "frame-src 'self' https://accounts.google.com https://challenges.cloudflare.com",
        ];

        if ($request->isSecure()) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }

    protected function buildPermissionsPolicy(Request $request): string
    {
        $cameraPolicy = $request->is('kiosk') || $request->is('kiosk/*')
            ? 'camera=(self)'
            : 'camera=()';

        return "{$cameraPolicy}, geolocation=(), microphone=()";
    }
}
