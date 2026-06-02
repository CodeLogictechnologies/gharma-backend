<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;

class Favicon extends Model
{
    protected $table = 'favicons';

    protected $fillable = [
        'image',
    ];
}