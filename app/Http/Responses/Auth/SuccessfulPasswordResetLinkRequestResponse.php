<?php

namespace App\Http\Responses\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;

class SuccessfulPasswordResetLinkRequestResponse implements SuccessfulPasswordResetLinkRequestResponseContract
{
    protected const RESET_LINK_COOLDOWN_SECONDS = 60;

    public function __construct(
        protected string $status,
    ) {}

    public function toResponse($request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['message' => trans($this->status)], 200);
        }

        return back()
            ->withInput($request->only('email'))
            ->with('status', trans($this->status))
            ->with(
                'password_reset_resend_available_at',
                now()->addSeconds(self::RESET_LINK_COOLDOWN_SECONDS)->timestamp,
            );
    }
}
