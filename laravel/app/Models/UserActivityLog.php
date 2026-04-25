<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class UserActivityLog extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'user_activity_logs';

    protected $fillable = [
        'idempotency_key',
        'user_id',
        'event',
        'data',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'data' => 'array',
    ];
}
