<?php

namespace App\Actions\Auth;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResolveGoogleAccount
{
    public function execute(?string $rawEmail): string
    {
        if ($rawEmail === null) {
            throw $this->invalidEmailException();
        }

        $normalizedEmail = Str::lower(trim($rawEmail));

        if ($normalizedEmail === '' || ! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw $this->invalidEmailException();
        }

        return $normalizedEmail;
    }

    protected function invalidEmailException(): ValidationException
    {
        return ValidationException::withMessages([
            'email' => 'Akun Google Anda tidak memiliki email yang valid.',
        ]);
    }
}
