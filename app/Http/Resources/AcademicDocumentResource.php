<?php

namespace App\Http\Resources;

use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\Thesis;
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
            'filePath' => $this->resolveSecureFilePath(),
            'companyName' => $this->company_name ?? null,
            'academicAdvisor' => $this->academic_advisor ?? null,
            'fieldAdvisor' => $this->field_advisor ?? null,
            'viewCount' => (int) $this->view_count,
            'keywords' => $this->keywords
                ? array_map('trim', explode(',', $this->keywords))
                : [],
        ];
    }

    protected function resolveSecureFilePath(): ?string
    {
        if (! filled($this->file_path)) {
            return null;
        }

        // Generate secure route URL with member-guard instead of direct public file link
        return match (true) {
            $this->resource instanceof Skripsi => route('skripsi.file', $this->student_id),
            $this->resource instanceof InternshipReport => route('internship-reports.file', $this->student_id),
            $this->resource instanceof Thesis => route('thesis.file', $this->student_id),
            default => null,
        };
    }
}
