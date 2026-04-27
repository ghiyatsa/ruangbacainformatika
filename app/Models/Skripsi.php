<?php

namespace App\Models;

use Database\Factories\SkripsiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];
}
