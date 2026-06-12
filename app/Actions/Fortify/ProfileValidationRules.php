<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\CampusEmail;
use App\Support\WhatsAppPhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'whatsapp' => $this->whatsappRules(),
            'address' => $this->addressRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(bool $required = true, int $max = 255): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:3',
            "max:{$max}",
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $normalized = Str::of($value)->squish()->toString();

                if (! preg_match("/^[\\pL\\pM\\s'.-]+$/u", $normalized)) {
                    $fail('Nama hanya boleh berisi huruf, spasi, titik, tanda petik, atau tanda hubung.');

                    return;
                }

                if (preg_match_all('/\pL/u', $normalized) < 2) {
                    $fail('Nama wajib diisi dengan benar.');
                }
            },
        ];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        $campusEmail = app(CampusEmail::class);

        return [
            'required',
            'string',
            'email',
            'max:255',
            function (string $attribute, mixed $value, \Closure $fail) use ($campusEmail): void {
                $message = $campusEmail->validationMessage(is_string($value) ? $value : null);

                if ($message !== null) {
                    $fail($message);
                }
            },
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user WhatsApp numbers.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function whatsappRules(bool $required = false, ?int $ignoreId = null): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:10',
            'max:15',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                if (! preg_match('/^08[1-9][0-9]{7,11}$/', $value)) {
                    $fail('Masukkan nomor WhatsApp yang valid, misalnya 08123456789.');
                }
            },
            $ignoreId === null
                ? Rule::unique(User::class, 'whatsapp')
                : Rule::unique(User::class, 'whatsapp')->ignore($ignoreId),
        ];
    }

    /**
     * Get the validation rules used to validate general phone numbers.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function phoneRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:10',
            'max:15',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                if (! preg_match('/^08[1-9][0-9]{7,11}$/', $value)) {
                    $fail('Masukkan nomor telepon yang valid, misalnya 08123456789.');
                }
            },
        ];
    }

    /**
     * Get the validation rules used to validate user addresses.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function addressRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:12',
            'max:500',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $normalized = Str::of($value)->squish()->toString();

                if (! preg_match('/\pL/u', $normalized)) {
                    $fail('Alamat wajib memuat keterangan yang jelas.');

                    return;
                }

                if ($this->countMeaningfulSegments($normalized) < 2) {
                    $fail('Alamat minimal terdiri dari dua bagian yang jelas.');
                }
            },
        ];
    }

    /**
     * Get the validation rules used to validate institution names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function institutionRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:3',
            'max:255',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $normalized = Str::of($value)->squish()->toString();

                if (! preg_match('/\pL/u', $normalized)) {
                    $fail('Nama instansi wajib diisi dengan jelas.');
                }
            },
        ];
    }

    /**
     * Get the validation rules used to validate free-form text fields.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function meaningfulTextRules(
        string $label,
        bool $required = true,
        int $min = 10,
        int $max = 1000,
        int $minWords = 2,
    ): array {
        return [
            $required ? 'required' : 'nullable',
            'string',
            "min:{$min}",
            "max:{$max}",
            function (string $attribute, mixed $value, \Closure $fail) use ($label, $minWords): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $normalized = Str::of($value)->squish()->toString();

                if (! preg_match('/\pL/u', $normalized)) {
                    $fail("{$label} wajib ditulis dengan jelas.");

                    return;
                }

                if ($this->countMeaningfulSegments($normalized) < $minWords) {
                    $fail("{$label} minimal terdiri dari {$minWords} kata.");
                }
            },
        ];
    }

    protected function normalizePhoneNumber(?string $value): string
    {
        return app(WhatsAppPhoneNumber::class)->normalize($value) ?? '';
    }

    protected function countMeaningfulSegments(string $value): int
    {
        preg_match_all('/[\pL\pM0-9]+/u', $value, $matches);

        return count($matches[0]);
    }
}
