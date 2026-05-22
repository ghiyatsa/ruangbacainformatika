<?php

namespace App\Http\Resources;

use App\Models\Thesis;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Thesis */
class ThesisResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'authorName' => $this->author_name,
            'studentId' => $this->student_id,
            'year' => $this->year ? (int) $this->year : null,
            'abstract' => $this->abstract,
            'viewCount' => (int) $this->view_count,
            'keywords' => $this->keywords
                ? array_map('trim', explode(',', $this->keywords))
                : [],
        ];
    }
}
