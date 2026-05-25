<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MemberRegistrationClaim;
use App\Services\MemberRegistrationClaimService;
use Illuminate\Http\RedirectResponse;

class MemberRegistrationClaimController extends Controller
{
    public function __construct(
        protected MemberRegistrationClaimService $claimService,
    ) {}

    public function show(string $token): RedirectResponse
    {
        $claim = $this->claimService->findByToken($token);

        if (! $claim instanceof MemberRegistrationClaim || $claim->status !== MemberRegistrationClaim::STATUS_PENDING) {
            return redirect()->route('home');
        }

        if ($claim->isExpired()) {
            $claim->markAsExpired();

            return redirect()->route('home');
        }

        return redirect()->route('auth.google', ['link_token' => $token]);
    }
}
