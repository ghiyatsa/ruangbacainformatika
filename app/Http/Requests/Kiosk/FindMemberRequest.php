<?php

namespace App\Http\Requests\Kiosk;

use Illuminate\Foundation\Http\FormRequest;

class FindMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function validatedIdentifier(): string
    {
        return $this->string('identifier')->trim()->toString();
    }
}
