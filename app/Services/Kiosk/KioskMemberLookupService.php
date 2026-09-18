<?php

declare(strict_types=1);

namespace App\Services\Kiosk;

use App\Models\User;
use App\Services\KioskLoanService;

class KioskMemberLookupService
{
    public function __construct(
        protected KioskLoanService $kioskLoanService,
    ) {}

    /**
     * Ringkasan publik seorang anggota untuk petugas kiosk.
     *
     * Email sengaja TIDAK dikembalikan utuh (hanya penanda boolean) agar endpoint
     * pencarian tidak bisa dipakai memanen alamat email anggota. Nomor WhatsApp
     * tetap disamarkan.
     *
     * @return array{name: string, hasEmail: bool, emailDomain: string|null, whatsappMasked: string|null}|null
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
            'hasEmail' => filled($member->email),
            'emailDomain' => $this->emailDomain($member->email),
            'whatsappMasked' => $this->maskPhoneNumber($member->whatsapp),
        ];
    }

    /**
     * Hanya domain email (mis. "mhs.unimal.ac.id") — cukup untuk memastikan
     * anggota memakai email kampus tanpa membocorkan identitasnya.
     */
    protected function emailDomain(?string $email): ?string
    {
        if (! is_string($email) || ! str_contains($email, '@')) {
            return null;
        }

        $domain = substr(strrchr($email, '@'), 1);

        return $domain !== '' ? $domain : null;
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
