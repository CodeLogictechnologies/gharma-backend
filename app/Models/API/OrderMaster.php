<?php

namespace App\Models\API;

use App\Models\BackPanel\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class OrderMaster extends Model
{

    public $incrementing = false;
    protected $keyType = 'string';
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'ordermasterid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'addressid');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'orgid');
    }
    public static function getOrderStatus($post)
    {
        try {
            $result = DB::table('order_masters')->where('orgid', $post['orgid'])
                ->select('order_status')
                ->where('userid', $post['userid'])->first();

            if (!$result) {
                throw new Exception('No Order Placed.', 1);
            }

            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}