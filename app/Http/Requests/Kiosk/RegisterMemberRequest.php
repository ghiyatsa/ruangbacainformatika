<?php

namespace App\Http\Requests\Kiosk;

use App\Actions\Fortify\ProfileValidationRules;
use App\Models\User;
use App\Support\CampusEmail;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterMemberRequest extends FormRequest
{
    use ProfileValidationRules;

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
            'name' => $this->nameRules(),
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $message = app(CampusEmail::class)->borrowingEligibilityMessage(is_string($value) ? $value : null);

                    if ($message !== null) {
                        $fail($message);
                    }
                },
            ],
            'whatsapp' => $this->whatsappRules(required: true),
            'address' => $this->addressRules(required: true),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::of((string) $this->input('name'))->squish()->toString(),
            'email' => Str::of((string) $this->input('email'))->trim()->lower()->toString(),
            'whatsapp' => $this->normalizePhoneNumber((string) $this->input('whatsapp')),
            'address' => Str::of((string) $this->input('address'))->squish()->toString(),
        ]);
    }
}
