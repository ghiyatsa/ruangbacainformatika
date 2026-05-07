<?php

namespace App\Support;

use Illuminate\Http\Request;
use Laravel\Fortify\Features;

class LoginViewData
{
    /**
     * @return array{canResetPassword: bool, canRegister: bool, canLoginWithGoogle: bool, status: string|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'canRegister' => Features::enabled(Features::registration()),
            'canLoginWithGoogle' => $this->canLoginWithGoogle(),
            'status' => $request->session()->get('status'),
        ];
    }

    public function canLoginWithGoogle(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
