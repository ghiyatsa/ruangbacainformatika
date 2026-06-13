<?php

namespace App\Actions\Loans;

use App\Models\User;
use App\Services\ReturnDraftService;

class GenerateReturnDraftQr
{
    public function __construct(
        protected ReturnDraftService $returnDraftService,
    ) {}

    /**
     * @param  array<int, int>  $selectedLoanItemIds
     * @return array<string, mixed>
     */
    public function execute(User $user, array $selectedLoanItemIds): array
    {
        return $this->returnDraftService->generateQr($user, $selectedLoanItemIds);
    }
}
