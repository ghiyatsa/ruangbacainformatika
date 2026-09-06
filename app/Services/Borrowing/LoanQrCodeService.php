<?php

namespace App\Services\Borrowing;

use App\Support\QrCodeWithLogo;

class LoanQrCodeService
{
    public function generateSvg(string $payload): string
    {
        return QrCodeWithLogo::generateSvg(
            payload: $payload,
            size: 192,
            withLogo: true,
            fillColor: 'currentColor',
            bgColor: 'transparent'
        );
    }
}
