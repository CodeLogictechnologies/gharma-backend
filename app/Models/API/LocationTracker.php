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
}