<?php

namespace App\Http\Requests\Kiosk;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RegisterMemberRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'whatsapp' => ['required', 'string', 'min:10', 'max:15'],
            'address' => ['required', 'string', 'max:1000'],
            'password' => ['required', 'string'],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::of((string) $this->input('name'))->squish()->toString(),
            'email' => Str::of((string) $this->input('email'))->trim()->lower()->toString(),
            'whatsapp' => Str::of((string) $this->input('whatsapp'))->replaceMatches('/\s+/', '')->toString(),
            'address' => Str::of((string) $this->input('address'))->squish()->toString(),
        ]);
    }
}
