<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Support\Kiosk\KioskPinManager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $normalizedInput = [
            ...$input,
            'name' => Str::of((string) ($input['name'] ?? ''))->squish()->toString(),
            'email' => Str::lower(trim((string) ($input['email'] ?? ''))),
            'whatsapp' => trim((string) ($input['whatsapp'] ?? '')),
        ];

        Validator::make($normalizedInput, [
            ...$this->profileRules(),
            'whatsapp' => $this->whatsappRules(required: true),
            'password' => $this->passwordRules(),
        ])->validate();

        $isKioskRegistration = app(KioskPinManager::class)->isVerified(request());

        $user = User::create([
            'name' => $normalizedInput['name'],
            'email' => $normalizedInput['email'],
            'password' => $normalizedInput['password'],
            'whatsapp' => $normalizedInput['whatsapp'] ?: null,
            'is_approved' => $isKioskRegistration || str_ends_with($normalizedInput['email'], '@mhs.unimal.ac.id'),
        ]);

        if (Role::query()->where('name', 'member')->exists() && $user->shouldReceiveMemberRole()) {
            $user->assignRole('member');
        }

        return $user;
    }
}
