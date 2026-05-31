<?php

namespace App\Http\Resources;

use App\Models\Loan;
use App\Support\AppTimezone;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $loan = $this->loan;
        $returnedAt = $this->returned_at;
        $dueAt = $loan?->due_at;
        $status = $this->isReturned() ? Loan::STATUS_RETURNED : Loan::STATUS_BORROWED;
        $isOverdue = $dueAt instanceof CarbonInterface
            && ($returnedAt ?? now())->greaterThan($dueAt);

        return [
            'id' => $this->id,
            'loanId' => $this->loan_id,
            'bookTitle' => $this->bookItem->book->title,
            'bookSlug' => $this->bookItem->book->slug,
            'internalCode' => $this->bookItem->internal_code,
            'borrowedAt' => AppTimezone::format($loan?->borrowed_at, 'd F Y H:i'),
            'dueAt' => AppTimezone::format($dueAt, 'd F Y H:i'),
            'returnedAt' => AppTimezone::format($returnedAt, 'd F Y H:i'),
            'status' => $status,
            'statusLabel' => $status === Loan::STATUS_RETURNED
                ? 'Dikembalikan'
                : 'Sedang Dipinjam',
            'isOverdue' => $isOverdue,
            'isReturned' => $this->isReturned(),
        ];
    }
}
