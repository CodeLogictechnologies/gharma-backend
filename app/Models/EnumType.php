<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EnumType extends Model
{
    protected $table = 'enum_types';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Ordered list of order status values, taken from the enum_types table (group_name = 'order_status')
     */
    public static function orderStatuses(): array
    {
        return Cache::remember('enum_types.order_status', 3600, function () {
            return static::where('group_name', 'order_status')
                ->orderBy('created_at')
                ->pluck('value')
                ->toArray();
        });
    }
}
