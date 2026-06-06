<?php

namespace App\Notifications\Messages;

class WhatsAppMessage
{
    public function __construct(
        public string $content,
        public bool $bypassPacing = false,
        public string $category = 'general',
        public ?string $templateName = null,
    ) {}
}
