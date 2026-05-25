<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class GoogleIdTokenVerifier
{
    /**
     * @return array{sub: string, email: string, name: string|null}
     */
    public function verify(string $credential): array
    {
        try {
            /** @var array<string, mixed> $decoded */
            $decoded = (array) JWT::decode($credential, JWK::parseKeySet($this->googleKeySet()));
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'credential' => 'Token Google tidak valid. Silakan coba lagi.',
            ]);
        }

        $audience = Arr::get($decoded, 'aud');
        $issuer = Arr::get($decoded, 'iss');
        $email = Str::lower((string) Arr::get($decoded, 'email', ''));
        $emailVerified = filter_var(Arr::get($decoded, 'email_verified'), FILTER_VALIDATE_BOOL);
        $subject = (string) Arr::get($decoded, 'sub', '');

        if (! $this->audienceMatches($audience)) {
            throw ValidationException::withMessages([
                'credential' => 'Token Google tidak ditujukan untuk aplikasi ini.',
            ]);
        }

        if (! in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw ValidationException::withMessages([
                'credential' => 'Penerbit token Google tidak dikenali.',
            ]);
        }

        if ($subject === '' || $email === '' || ! $emailVerified) {
            throw ValidationException::withMessages([
                'credential' => 'Akun Google Anda belum dapat diverifikasi oleh sistem.',
            ]);
        }

        return [
            'sub' => $subject,
            'email' => $email,
            'name' => Arr::get($decoded, 'name'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function googleKeySet(): array
    {
        /** @var array<string, mixed> $keys */
        $keys = Cache::remember('google-id-token-keys', now()->addHour(), function (): array {
            $response = Http::acceptJson()
                ->timeout(10)
                ->get('https://www.googleapis.com/oauth2/v3/certs')
                ->throw();

            /** @var array<string, mixed> $json */
            $json = $response->json();

            if (! isset($json['keys']) || ! is_array($json['keys'])) {
                throw ValidationException::withMessages([
                    'credential' => 'Kunci verifikasi Google tidak tersedia saat ini.',
                ]);
            }

            return $json;
        });

        return $keys;
    }

    protected function audienceMatches(mixed $audience): bool
    {
        $configuredAudience = Config::string('services.google.client_id');

        if ($configuredAudience === '') {
            return false;
        }

        if (is_string($audience)) {
            return hash_equals($configuredAudience, $audience);
        }

        if (is_array($audience)) {
            return in_array($configuredAudience, $audience, true);
        }

        return false;
    }
}
