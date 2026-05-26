<?php

namespace App\Models;

use Database\Factories\InternshipReportFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipReport extends Model
{
    /** @use HasFactory<InternshipReportFactory> */
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

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
        ];
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('author_name', 'like', "%{$search}%")
                ->orWhere('keywords', 'like', "%{$search}%");
        });
    }
}
