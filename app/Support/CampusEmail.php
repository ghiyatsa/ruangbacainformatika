<?php

namespace App\Support;

use Illuminate\Support\Str;

class CampusEmail
{
    public function normalize(string $email): string
    {
        return Str::lower(trim($email));
    }

    public function validationMessage(?string $email): ?string
    {
        if ($email === null) {
            return 'Gunakan email UNIMAL yang valid.';
        }

        $normalizedEmail = $this->normalize($email);

        if ($normalizedEmail === '' || ! $this->isEligibleEmail($normalizedEmail)) {
            return 'Gunakan email UNIMAL yang valid.';
        }

        if ($this->isMahasiswaEmail($normalizedEmail) && ! $this->isAcceptedMahasiswaEmail($normalizedEmail)) {
            return 'Hanya mahasiswa Teknik Informatika.';
        }

        return null;
    }

    public function isEligibleEmail(string $email): bool
    {
        return str_ends_with($email, '@mhs.unimal.ac.id')
            || str_ends_with($email, '@unimal.ac.id');
    }

    public function isMahasiswaEmail(string $email): bool
    {
        return str_ends_with($email, '@mhs.unimal.ac.id');
    }

    public function isAcceptedMahasiswaEmail(string $email): bool
    {
        if (! $this->isMahasiswaEmail($email)) {
            return false;
        }

        return substr($this->extractIdentityNumber($email), 3, 3) === '170';
    }

    public function extractIdentityNumber(string $email): string
    {
        $emailPrefix = Str::before($this->normalize($email), '@');

        return str_contains($emailPrefix, '.')
            ? Str::afterLast($emailPrefix, '.')
            : $emailPrefix;
    }
}
