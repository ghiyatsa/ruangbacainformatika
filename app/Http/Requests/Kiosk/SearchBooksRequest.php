<?php

namespace App\Http\Requests\Kiosk;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SearchBooksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'mode' => ['nullable', 'string', 'in:borrow,return'],
            'member_identifier' => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $identifier = Str::of((string) $value)->trim()->lower()->toString();

                    if ($identifier === '') {
                        return;
                    }

                    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
                    $isNim = preg_match('/^\d{9}$/', $identifier) === 1;
                    $phoneDigits = preg_replace('/\D+/', '', $identifier);
                    $isPhone = preg_match('/^(?:0|62)?8[1-9][0-9]{7,11}$/', $phoneDigits) === 1;

                    if (! $isEmail && ! $isNim && ! $isPhone) {
                        $fail('Masukkan email lengkap, NIM 9 digit, atau nomor HP.');
                    }
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => Str::of((string) $this->input('q'))->squish()->toString(),
            'mode' => Str::of((string) $this->input('mode'))->lower()->trim()->toString(),
            'member_identifier' => Str::of((string) $this->input('member_identifier'))->trim()->lower()->toString(),
        ]);
    }

    public function validatedQuery(): string
    {
        return (string) $this->validated('q', '');
    }

    public function validatedMode(): string
    {
        return (string) $this->validated('mode', 'borrow');
    }

    public function validatedMemberIdentifier(): string
    {
        return (string) $this->validated('member_identifier', '');
    }
}
