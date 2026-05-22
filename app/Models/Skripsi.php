<?php

namespace App\Models;

use Database\Factories\SkripsiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Skripsi extends Model
{
    /** @use HasFactory<SkripsiFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'author_name',
        'student_id',
        'year',
        'abstract',
        'keywords',
        'view_count',
    ];

    protected $casts = [
        'view_count' => 'integer',
    ];

    public function similaritySyncStatus(): HasOne
    {
        return $this->hasOne(SimilaritySyncStatus::class, 'source_skripsi_id', 'id');
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
