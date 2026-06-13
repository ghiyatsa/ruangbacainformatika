<?php

namespace App\Services\Kiosk;

use App\Models\User;
use App\Services\KioskLoanService;

class KioskMemberLookupService
{
    public function __construct(
        protected KioskLoanService $kioskLoanService,
    ) {}

    /**
     * @return array{name: string, emailMasked: string|null, whatsappMasked: string|null}|null
     */
    public function preview(string $identifier): ?array
    {
        if (blank($identifier)) {
            return null;
        }

        $member = $this->kioskLoanService->findMemberByIdentifier($identifier);

        if (! $member instanceof User) {
            return null;
        }

        return [
            'name' => $member->name,
            'emailMasked' => $member->email,
            'whatsappMasked' => $this->maskPhoneNumber($member->whatsapp),
        ];
    }

    protected function maskPhoneNumber(?string $phoneNumber): ?string
    {
        if (! is_string($phoneNumber) || $phoneNumber === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phoneNumber) ?? '';
        $length = strlen($digits);

        if ($length < 4) {
            return null;
        }

        return substr($digits, 0, 4).str_repeat('*', max($length - 6, 1)).substr($digits, -2);
    }
}
