<?php

namespace App\Support;

class LoginViewData
{
    public function canLoginWithGoogle(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
