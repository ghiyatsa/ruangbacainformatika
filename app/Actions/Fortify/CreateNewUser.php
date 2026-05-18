<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\CampusEmail;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                function ($attribute, $value, $fail) {
                    $campusEmail = app(CampusEmail::class);
                    $message = $campusEmail->validationMessage($value);

                    if ($message) {
                        $fail($message);
                    }
                },
            ],
            'whatsapp' => ['nullable', 'string', 'min:10', 'max:15'],
            'address' => ['nullable', 'string', 'max:1000'],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'whatsapp' => $input['whatsapp'] ?? null,
            'address' => $input['address'] ?? null,
            'password' => $input['password'],
        ]);

        $user->assignMemberRoleIfAvailable();

        return $user;
    }
}
