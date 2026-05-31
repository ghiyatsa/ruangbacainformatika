<?php

namespace App\Http\Resources;

use App\Models\Loan;
use App\Support\AppTimezone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'statusLabel' => ($this->isOverdue() && $this->status === Loan::STATUS_BORROWED)
                ? 'Terlambat'
                : (Loan::statusOptions()[$this->status] ?? $this->status),
            'borrowedAt' => AppTimezone::format($this->borrowed_at, 'd F Y H:i'),
            'dueAt' => AppTimezone::format($this->due_at, 'd F Y H:i'),
            'returnedAt' => AppTimezone::format($this->returned_at, 'd F Y H:i'),
            'isOverdue' => $this->isOverdue(),
            'items' => LoanItemResource::collection($this->whenLoaded('items'))->resolve(),
            'itemsCount' => $this->items_count ?? $this->items()->count(),
        ];
    }
}
