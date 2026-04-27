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
            'isbn' => [
                'required',
                'string',
                'max:20',
                Rule::exists(Book::class, 'isbn'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'member_identifier' => Str::of((string) $this->input('member_identifier'))->squish()->toString(),
            'isbn' => Str::of((string) $this->input('isbn'))->trim()->toString(),
        ]);
    }

    public function validatedIsbn(): string
    {
        return (string) $this->validated('isbn');
    }
}
