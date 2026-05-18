<?php

namespace App\Http\Middleware;

use App\Support\KioskNetworkGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKioskNetworkIsAllowed
{
    public function __construct(
        protected KioskNetworkGuard $kioskNetworkGuard,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $this->kioskNetworkGuard->allows($request),
            403,
            'Akses kiosk hanya diizinkan dari jaringan internal perpustakaan.',
        );

        return $next($request);
    }
}
