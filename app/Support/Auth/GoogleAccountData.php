<?php

namespace App\Support\Auth;

final class GoogleAccountData
{
    public function __construct(
        public string $email,
        public bool $isApproved,
    ) {}
}
