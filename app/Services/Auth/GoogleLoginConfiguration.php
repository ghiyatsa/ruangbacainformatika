<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Config;

class GoogleLoginConfiguration
{
    public function isConfigured(): bool
    {
        return filled(Config::string('services.google.client_id'))
            && filled(Config::string('services.google.client_secret'))
            && filled(Config::string('services.google.redirect'));
    }
}
