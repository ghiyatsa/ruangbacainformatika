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
            'member_identifier' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => Str::of((string) $this->input('q'))->squish()->toString(),
            'mode' => Str::of((string) $this->input('mode'))->lower()->trim()->toString(),
            'member_identifier' => Str::of((string) $this->input('member_identifier'))->squish()->toString(),
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
