<?php

namespace App\Http\Requests\Kiosk;

use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BorrowBookRequest extends KioskBookActionRequest
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

                    if (! $isEmail && ! $isNim) {
                        $fail('Masukkan email lengkap atau NIM 9 digit.');
                    }
                },
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
}
