<?php

namespace App\Models;

use Database\Factories\ThesisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thesis extends Model
{
    /** @use HasFactory<ThesisFactory> */
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
}
