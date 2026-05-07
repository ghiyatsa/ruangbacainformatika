<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'bookTitle' => $this->bookItem->book->title,
            'bookSlug' => $this->bookItem->book->slug,
            'internalCode' => $this->bookItem->internal_code,
            'returnedAt' => $this->returned_at?->translatedFormat('d F Y H:i') ?? '-',
            'isReturned' => $this->isReturned(),
        ];
    }
}
