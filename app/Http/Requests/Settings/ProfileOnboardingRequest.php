<?php

namespace App\Http\Requests\Settings;

use App\Actions\Fortify\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileOnboardingRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'whatsapp' => $this->whatsappRules(required: true, ignoreId: $this->user()?->id),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'whatsapp' => trim((string) $this->input('whatsapp')),
        ]);
    }
}
