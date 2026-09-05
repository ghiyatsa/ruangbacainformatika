<?php

namespace App\Http\Requests\Settings;

use App\Actions\Fortify\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'whatsapp' => ['sometimes', ...$this->whatsappRules(required: false, ignoreId: $this->user()?->id)],
            'address' => $this->addressRules(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'name' => Str::of((string) $this->input('name'))->squish()->toString(),
            'address' => Str::of((string) $this->input('address'))->squish()->toString(),
        ];

        if ($this->has('whatsapp')) {
            $data['whatsapp'] = $this->normalizePhoneNumber((string) $this->input('whatsapp'));
        }

        $this->merge($data);
    }
}
