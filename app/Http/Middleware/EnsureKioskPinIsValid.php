<?php

namespace App\Http\Middleware;

use App\Support\Kiosk\KioskPinManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKioskPinIsValid
{
    public function __construct(
        protected KioskPinManager $kioskPinManager,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->kioskPinManager->isVerified($request)) {
            return $next($request);
        }

        return redirect()
            ->route('kiosk.index')
            ->withErrors([
                'pin' => 'Masukkan PIN kiosk terlebih dahulu.',
            ]);
    }
}
