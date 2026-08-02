<?php

namespace App\Models;

use App\Models\Concerns\SearchableAcademic;
use Database\Factories\SkripsiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Skripsi extends Model
{
    /** @use HasFactory<SkripsiFactory> */
    use HasFactory, SearchableAcademic;

    protected $fillable = [
        'title',
        'author_name',
        'student_id',
        'year',
        'abstract',
        'keywords',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
        ];
    }

    public function similaritySyncStatus(): MorphOne
    {
        return $this->morphOne(SimilaritySyncStatus::class, 'syncable');
    }

    public function similaritySyncStatusLabel(): string
    {
        return $this->similaritySyncStatus?->statusLabel() ?? 'Belum';
    }

    public function similaritySyncStatusColor(): string
    {
        return $this->similaritySyncStatus?->statusColor() ?? 'gray';
    }
}
