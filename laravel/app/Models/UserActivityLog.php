<?php

namespace App\Models;

use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Model;

class UserActivityLog extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'user_activity_logs';

    protected $primaryKey = '_id';

    protected $keyType = 'string';

    public $incrementing = false;

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

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        if ($field === '_id' && is_string($value) && strlen($value) === 24) {
            $value = new ObjectId($value);
        }

        return $query->where($field, $value);
    }
}
