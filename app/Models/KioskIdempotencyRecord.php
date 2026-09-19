<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KioskIdempotencyRecord extends Model
{
    protected $fillable = [
        'key',
        'fingerprint',
        'status_code',
        'response_body',
        'expires_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'response_body' => 'array',
        'expires_at' => 'datetime',
    ];
}
