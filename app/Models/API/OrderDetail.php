<?php

namespace App\Models\API;

use App\Models\BackPanel\Item;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{

    public $incrementing = false;
    protected $keyType = 'string';

    public function orderMaster()
    {
        return $this->belongsTo(OrderMaster::class, 'ordermasterid');
    }

    public function variation()
    {
        return $this->belongsTo(Item::class, 'variation_id');
    }
}