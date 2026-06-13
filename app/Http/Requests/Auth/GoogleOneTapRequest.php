<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class GoogleOneTapRequest extends FormRequest
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
            'credential' => ['required', 'string', 'max:8192'],
            'link_token' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function validatedCredential(): string
    {
        return (string) $this->validated('credential');
    }

    public function validatedLinkToken(): ?string
    {
        $linkToken = $this->string('link_token')->trim()->toString();

        return $linkToken !== '' ? $linkToken : null;
    }
}
