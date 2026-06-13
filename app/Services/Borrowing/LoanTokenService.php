<?php

namespace App\Services\Borrowing;

use Illuminate\Support\Str;

class LoanTokenService
{
    protected const OPAQUE_TOKEN_LENGTH = 48;

    public function make(string $prefix): string
    {
        return $prefix.Str::random(self::OPAQUE_TOKEN_LENGTH);
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * @param  array<int, string>  $prefixes
     */
    public function extract(string $payload, array $prefixes): ?string
    {
        $normalized = Str::of($payload)->trim()->toString();

        if ($normalized === '') {
            return null;
        }

        if ($this->isReadable($normalized, $prefixes)) {
            return $normalized;
        }

        if (filter_var($normalized, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $query = parse_url($normalized, PHP_URL_QUERY);

        if (! is_string($query)) {
            return null;
        }

        parse_str($query, $queryParams);

        $token = $queryParams['token'] ?? null;

        return is_string($token) && $this->isReadable($token, $prefixes)
            ? $token
            : null;
    }

    /**
     * @param  array<int, string>  $prefixes
     */
    public function isReadable(string $token, array $prefixes): bool
    {
        if (Str::startsWith($token, $prefixes)) {
            return true;
        }

        return preg_match('/\A[A-Za-z0-9]{80,160}\z/', $token) === 1;
    }
}
