<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    protected $fillable = [
        'user_id',
        'ip',
        'machine',
        'system',
        'browser',
        'model',
        'action',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
