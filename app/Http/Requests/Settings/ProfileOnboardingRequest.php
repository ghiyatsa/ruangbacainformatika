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
        $user = $this->user();

        return [
            'whatsapp' => $this->whatsappRules(required: blank($user?->whatsapp), ignoreId: $user?->id),
            'address' => $this->addressRules(required: blank($user?->address)),
        ];
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        $this->merge([
            'whatsapp' => trim((string) ($this->input('whatsapp') ?: $user?->whatsapp)),
            'address' => str((string) ($this->input('address') ?: $user?->address))->squish()->toString(),
        ]);
    }
}
