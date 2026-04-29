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

    public const KIOSK_RETURN_AFTER_VERIFICATION_KEY = 'kiosk.return_after_verification';

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

        if ($this->shouldReturnToKiosk($request->input('redirect_to'))) {
            $request->session()->put(self::KIOSK_RETURN_AFTER_VERIFICATION_KEY, true);
        }

        return Inertia::location($this->authenticationRedirector->redirectResponse($request));
    }

    protected function shouldReturnToKiosk(mixed $redirectTo): bool
    {
        if (! is_string($redirectTo)) {
            return false;
        }

        return parse_url($redirectTo, PHP_URL_PATH) === route('kiosk.index', absolute: false);
    }
}
