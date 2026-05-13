<?php

namespace App\Support;

use Throwable;

class MailDeliveryIssue
{
    public static function isRateLimited(Throwable $exception): bool
    {
        $message = str($exception->getMessage())->lower()->toString();

        return str($message)->contains([
            'rate limit',
            'too many requests',
            'quota',
            'daily limit',
            'throttl',
            'maximum sending rate',
            'exceeded your sending limits',
        ]);
    }

    public static function verificationMessage(Throwable $exception): string
    {
        if (self::isRateLimited($exception)) {
            return 'Layanan email sedang mencapai batas pengiriman harian. Kami belum bisa mengirim OTP sekarang. Silakan coba lagi beberapa saat.';
        }

        return 'Layanan email sedang bermasalah. Kami belum bisa mengirim OTP saat ini. Silakan coba lagi beberapa saat.';
    }

    public static function queuedNotice(): string
    {
        return 'Jika layanan email sedang sibuk atau mencapai batas harian, sistem akan mencoba mengirim ulang secara otomatis.';
    }
}
