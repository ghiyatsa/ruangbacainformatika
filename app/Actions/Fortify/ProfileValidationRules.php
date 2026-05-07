<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\CampusEmail;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'whatsapp' => $this->whatsappRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        $campusEmail = app(CampusEmail::class);

        return [
            'required',
            'string',
            'email',
            'max:255',
            function (string $attribute, mixed $value, \Closure $fail) use ($campusEmail): void {
                $message = $campusEmail->validationMessage(is_string($value) ? $value : null);

                if ($message !== null) {
                    $fail($message);
                }
            },
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user WhatsApp numbers.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function whatsappRules(bool $required = false, ?int $ignoreId = null): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'digits_between:10,15',
            $ignoreId === null
                ? Rule::unique(User::class, 'whatsapp')
                : Rule::unique(User::class, 'whatsapp')->ignore($ignoreId),
        ];
    }
}
