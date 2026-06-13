<?php

namespace App\Actions\Kiosk;

use App\Services\MemberRegistrationClaimService;
use Illuminate\Http\Request;

class ResolveKioskMemberRegistrationClaim
{
    public function __construct(
        protected MemberRegistrationClaimService $memberRegistrationClaimService,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function execute(Request $request): ?array
    {
        $presentedClaim = $request->session()->get('kiosk.member_registration_claim');

        if (! is_array($presentedClaim)) {
            return null;
        }

        $claim = $this->memberRegistrationClaimService->syncPresentedClaim($presentedClaim);

        if ($claim === null) {
            $request->session()->forget('kiosk.member_registration_claim');

            return null;
        }

        $request->session()->put('kiosk.member_registration_claim', $claim);

        return $claim;
    }
}
