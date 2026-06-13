<?php

namespace App\Actions\Loans;

use App\Services\ReturnDraftService;

class ConsumeReturnDraft
{
    public function __construct(
        protected ReturnDraftService $returnDraftService,
    ) {}

    public function execute(string $payload): int
    {
        return $this->returnDraftService->consume($payload);
    }
}
