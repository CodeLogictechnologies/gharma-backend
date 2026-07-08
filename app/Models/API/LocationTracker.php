<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class LocationTracker extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveLocation($post)
    {
        try {
            $insertOrderMaster = [
                'id'         => (string) Str::uuid(),
                'orgid'      => $post['orgid'],
                'riderid'     => $post['userid'],
                'latitude'      => $post['latitude'],
                'longitude'      => $post['longitude'],
                'orderid'      => $post['order_id'],
                'created_at' => Carbon::now(),
            ];

            if (!LocationTracker::insert($insertOrderMaster)) {
                throw new \Exception("Couldn't save location.");
            }

            $data = DB::table('location_trackers')
                ->select('longitude', 'latitude')
                ->where('riderid', $post['userid'])
                ->first();
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function getLocation($post)
    {
        try {

            $getMaster = DB::table('order_details')
                ->where('id', $post['order_id'])
                ->select('ordermasterid')
                ->first();

            $data = DB::table('location_trackers')
                ->where('orderid', $getMaster->ordermasterid)
                ->select('longitude', 'latitude')
                ->latest()
                ->first();
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getLocationCustomer($post)
    {
        try {

            $getMaster = DB::table('order_details')
                ->where('id', $post['order_id'])
                ->select('ordermasterid')
                ->first();

            $data = DB::table('order_masters as om')
                ->join('user_addresses as ua', function ($join) {
                    $join->on('ua.userid', '=', 'om.userid')
                        ->on('ua.id', '=', 'om.addressid');
                })
                ->where('om.id', $getMaster->ordermasterid)
                ->select(
                    'ua.longitude',
                    'ua.latitude',
                    // 'ua.address_name',
                    // 'om.id as order_id',
                )
                ->first();
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
