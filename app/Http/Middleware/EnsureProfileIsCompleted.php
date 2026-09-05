<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Auth\AuthenticationRedirector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsCompleted
{
    public function __construct(
        protected AuthenticationRedirector $authenticationRedirector,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('loans/request*')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user instanceof User && $this->authenticationRedirector->requiresProfileCompletion($user, allowSkipped: false)) {
            $request->session()->put('url.intended', $request->fullUrl());

            return $request->expectsJson()
                ? abort(403, 'Your profile is incomplete.')
                : redirect()->route('register.profile');
        }

        return $next($request);
    }
}
