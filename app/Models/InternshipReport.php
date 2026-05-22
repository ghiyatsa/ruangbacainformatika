<?php

namespace App\Models;

use Database\Factories\InternshipReportFactory;
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

    protected $casts = [
        'view_count' => 'integer',
    ];
}
