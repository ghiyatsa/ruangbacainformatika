<?php

namespace App\Http\Requests\Kiosk;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ConsumeReturnDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payload' => ['required', 'string', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'payload' => Str::of((string) $this->input('payload'))->trim()->toString(),
        ]);
    }

    public function validatedPayload(): string
    {
        return (string) $this->validated('payload');
    }
}
