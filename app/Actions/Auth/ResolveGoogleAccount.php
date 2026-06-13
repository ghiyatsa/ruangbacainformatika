<?php

namespace App\Actions\Auth;

use App\Support\CampusEmail;
use App\Support\GoogleAccountData;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResolveGoogleAccount
{
    public function __construct(
        protected CampusEmail $campusEmail,
    ) {}

    public function execute(?string $rawEmail): GoogleAccountData
    {
        if ($rawEmail === null) {
            throw $this->invalidEmailException();
        }

        $normalizedEmail = Str::lower(trim($rawEmail));

        if ($normalizedEmail === '' || ! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw $this->invalidEmailException();
        }

        return new GoogleAccountData(
            email: $normalizedEmail,
            isApproved: $this->campusEmail->shouldAutoApprove($normalizedEmail),
        );
    }

    protected function invalidEmailException(): ValidationException
    {
        return ValidationException::withMessages([
            'email' => 'Akun Google Anda tidak memiliki email yang valid.',
        ]);
    }
}
