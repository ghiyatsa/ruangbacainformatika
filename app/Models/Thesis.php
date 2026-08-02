<?php

namespace App\Models;

use App\Models\Concerns\SearchableAcademic;
use Database\Factories\ThesisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thesis extends Model
{
    /** @use HasFactory<ThesisFactory> */
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
}
