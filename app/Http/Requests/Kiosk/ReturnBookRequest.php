<?php

namespace App\Http\Requests\Kiosk;

use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReturnBookRequest extends KioskBookActionRequest
{
    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'member_identifier' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $identifier = Str::of((string) $value)->trim()->lower()->toString();
                    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
                    $isNim = preg_match('/^\d{9}$/', $identifier) === 1;
                    $phoneDigits = preg_replace('/\D+/', '', $identifier);
                    $isPhone = preg_match('/^(?:0|62)?8[1-9][0-9]{7,11}$/', $phoneDigits) === 1;

                    if (! $isEmail && ! $isNim && ! $isPhone) {
                        $fail('Masukkan email lengkap, NIM 9 digit, atau nomor HP.');
                    }
                },
            ],
            'verification_payload' => [
                'required',
                'string',
                'max:2048',
            ],
            'book_ids' => ['required', 'array', 'min:1'],
            'book_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(Book::class, 'id'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $bookIds = collect($this->input('book_ids', []))
            ->map(fn (mixed $bookId): ?int => is_numeric($bookId)
                ? (int) $bookId
                : null)
            ->filter(fn (?int $bookId): bool => $bookId !== null)
            ->values()
            ->all();

        $this->merge([
            'member_identifier' => Str::of((string) $this->input('member_identifier'))->trim()->lower()->toString(),
            'verification_payload' => Str::of((string) $this->input('verification_payload'))->trim()->toString(),
            'book_ids' => $bookIds,
        ]);
    }

    /**
     * @return array<int, int>
     */
    public function validatedBookIds(): array
    {
        return array_map('intval', (array) $this->validated('book_ids'));
    }

    public function validatedMemberIdentifier(): string
    {
        return (string) $this->validated('member_identifier');
    }

    public function validatedVerificationPayload(): string
    {
        return (string) $this->validated('verification_payload');
    }
}
