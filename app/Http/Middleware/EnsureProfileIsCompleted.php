<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->hasRequiredProfileDetails()) {
            return $request->expectsJson()
                ? abort(403, 'Your profile is incomplete.')
                : redirect()->route('register.profile');
        }

        return $next($request);
    }
}
