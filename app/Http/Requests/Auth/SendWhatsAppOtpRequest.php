<?php

namespace App\Http\Requests\Auth;

use App\Actions\Fortify\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendWhatsAppOtpRequest extends FormRequest
{
    use ProfileValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'whatsapp' => $this->whatsappRules(
                required: blank($user?->whatsapp),
                ignoreId: $user?->id,
            ),
        ];
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        $this->merge([
            'whatsapp' => $this->normalizePhoneNumber(
                (string) ($this->input('whatsapp') ?: $user?->whatsapp),
            ),
        ]);
    }
}
