<?php

namespace App\Support;

use Illuminate\Support\Str;

class CampusEmail
{
    public const TEKNIK_INFORMATIKA_PROGRAM_CODE = '170';

    public const ELIGIBLE_EMAIL_MESSAGE = 'Gunakan email UNIMAL dengan domain @mhs.unimal.ac.id atau @unimal.ac.id.';

    public function normalize(string $email): string
    {
        return Str::lower(trim($email));
    }

    public function validationMessage(?string $email): ?string
    {
        if ($email === null) {
            return self::ELIGIBLE_EMAIL_MESSAGE;
        }

        $normalizedEmail = $this->normalize($email);

        if ($normalizedEmail === '' || ! $this->isEligibleEmail($normalizedEmail)) {
            return self::ELIGIBLE_EMAIL_MESSAGE;
        }

        return null;
    }

    public function isEligibleEmail(string $email): bool
    {
        return str_ends_with($email, '@mhs.unimal.ac.id')
            || str_ends_with($email, '@unimal.ac.id');
    }

    public function requiresWhatsAppVerification(string $email): bool
    {
        return $this->isEligibleEmail($this->normalize($email));
    }

    public function isMahasiswaEmail(string $email): bool
    {
        return str_ends_with($email, '@mhs.unimal.ac.id');
    }

    public function isTeknikInformatikaStudentEmail(string $email): bool
    {
        if (! $this->isMahasiswaEmail($email)) {
            return false;
        }

        return $this->isTeknikInformatikaIdentityNumber(
            $this->extractIdentityNumber($email),
        );
    }

    public function borrowingEligibilityMessage(?string $email): ?string
    {
        if ($email === null) {
            return self::ELIGIBLE_EMAIL_MESSAGE;
        }

        $normalizedEmail = $this->normalize($email);

        if (! $this->isEligibleEmail($normalizedEmail)) {
            return self::ELIGIBLE_EMAIL_MESSAGE;
        }

        return null;
    }

    public function shouldAutoApprove(string $email): bool
    {
        return $this->isMahasiswaEmail($this->normalize($email));
    }

    public function extractIdentityNumber(string $email): string
    {
        $emailPrefix = Str::before($this->normalize($email), '@');

        return str_contains($emailPrefix, '.')
            ? Str::afterLast($emailPrefix, '.')
            : $emailPrefix;
    }

    public function isTeknikInformatikaIdentityNumber(string $identityNumber): bool
    {
        $normalizedIdentityNumber = preg_replace('/\D+/', '', $identityNumber) ?? '';

        return strlen($normalizedIdentityNumber) === 9
            && substr($normalizedIdentityNumber, 3, 3) === self::TEKNIK_INFORMATIKA_PROGRAM_CODE;
    }
}
