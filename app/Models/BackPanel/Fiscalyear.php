<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Fiscalyear extends Model
{
    use HasUuids;

    protected $table = 'fiscal_years';

    protected $fillable = [
        'code',
        'start_date',
        'end_date',
        'is_current',
        'status',
    ];

    protected static function booted()
    {
        // Whenever one fiscal year is marked current, unset it on all others
        static::saving(function (Fiscalyear $fy) {
            if ($fy->is_current === 'Y') {
                static::where('id', '!=', $fy->id)->update(['is_current' => 'N']);
            }
        });
    }
}