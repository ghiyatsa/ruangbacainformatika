<?php

namespace App\Notifications\Messages;

class WhatsAppMessage
{
    public function __construct(
        public string $content,
    ) {}
}
