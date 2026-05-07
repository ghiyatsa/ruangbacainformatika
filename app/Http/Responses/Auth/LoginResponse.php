<?php

namespace App\Http\Responses\Auth;

use App\Services\Auth\AuthenticationRedirector;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function __construct(
        protected AuthenticationRedirector $authenticationRedirector,
    ) {}

    public function toResponse($request): Response
    {
        return Inertia::location($this->authenticationRedirector->redirectResponse($request));
    }
}
