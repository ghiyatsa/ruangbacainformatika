<?php

namespace App\Notifications\Concerns;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Queue\Middleware\RateLimited;

trait RateLimitsWhatsAppNotifications
{
    /**
     * @return array<int, object>
     */
    public function middleware(object $notifiable, string $channel): array
    {
        if ($channel !== WhatsAppChannel::class) {
            return [];
        }

        return [
            (new RateLimited('whatsapp-notifications'))
                ->releaseAfter(max((int) config('services.fonnte.send_interval_seconds', 15), 5)),
        ];
    }
}
