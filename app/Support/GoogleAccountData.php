<?php

namespace App\Support;

final class GoogleAccountData
{
    public function __construct(
        public string $email,
        public bool $isApproved,
    ) {}
}
