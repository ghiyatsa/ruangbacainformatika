<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoanDraftQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
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
