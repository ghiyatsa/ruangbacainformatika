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

    public function validatedVerificationPayload(): string
    {
        return (string) $this->validated('verification_payload');
    }
}
