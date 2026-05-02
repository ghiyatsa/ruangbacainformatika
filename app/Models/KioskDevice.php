<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KioskDevice extends Model
{
    protected $fillable = [
        'session_id',
        'device_token',
        'ip_address',
        'user_agent',
        'name',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];
}
