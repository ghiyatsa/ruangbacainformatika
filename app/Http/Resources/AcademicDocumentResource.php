<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Base resource for academic document types (Skripsi, Thesis, InternshipReport).
 * All three share an identical column structure, so their toArray() output is the same.
 */
class AcademicDocumentResource extends JsonResource
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
