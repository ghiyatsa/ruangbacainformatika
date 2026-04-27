<?php

namespace App\Http\Responses\Auth;

use App\Support\Auth\AuthenticationRedirector;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    protected const VERIFICATION_RESEND_COOLDOWN_SECONDS = 60;

    public function __construct(
        protected AuthenticationRedirector $authenticationRedirector,
    ) {}

    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $request->session()->put(
            'verification_resend_available_at',
            now()->addSeconds(self::VERIFICATION_RESEND_COOLDOWN_SECONDS)->timestamp,
        );

        return Inertia::location($this->authenticationRedirector->redirectResponse($request));
    }
}
