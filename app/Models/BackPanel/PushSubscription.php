<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'orgid',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
