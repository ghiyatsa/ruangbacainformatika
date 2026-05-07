<?php

namespace App\Http\Requests\Kiosk;

use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

abstract class KioskBookActionRequest extends FormRequest
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
            'member_identifier' => ['required', 'string', 'max:255'],
            'isbns' => ['required', 'array', 'min:1'],
            'isbns.*' => [
                'required',
                'string',
                'max:20',
                Rule::exists(Book::class, 'isbn'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $isbns = collect($this->input('isbns', []))
            ->map(fn ($isbn) => Str::of((string) $isbn)->trim()->toString())
            ->filter()
            ->values()
            ->all();

        $this->merge([
            'member_identifier' => Str::of((string) $this->input('member_identifier'))->squish()->toString(),
            'isbns' => $isbns,
        ]);
    }

    /**
     * @return array<int, string>
     */
    public function validatedIsbns(): array
    {
        return (array) $this->validated('isbns');
    }
}
