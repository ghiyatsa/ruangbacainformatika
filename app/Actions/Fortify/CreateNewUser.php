<?php

namespace App\Actions\Fortify;

use App\Models\User;
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
                    if (! str_ends_with($value, '@unimal.ac.id') && ! str_ends_with($value, '@mhs.unimal.ac.id')) {
                        $fail('Email harus menggunakan domain @unimal.ac.id atau @mhs.unimal.ac.id');

                        return;
                    }

                    if (str_ends_with($value, '@mhs.unimal.ac.id')) {
                        $username = explode('@', $value)[0];
                        if (! str_starts_with($username, '23017')) {
                            $fail('Hanya mahasiswa Teknik Informatika (angkatan 23, prodi 017) yang dapat mendaftar.');
                        }
                    }
                },
            ],
            'whatsapp' => ['required', 'string', 'min:10', 'max:15'],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'whatsapp' => $input['whatsapp'],
            'password' => $input['password'],
        ]);
    }
}
