<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class UserAddress extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveData($post)
    {
        try {
            $insertUserAddress = [
                'id'         => (string) Str::uuid(),
                'orgid'      => $post['orgid'],
                'userid'     => $post['userid'],
                'title'     => $post['title'],
                'name'     => $post['name'],
                'address_name'     => $post['address_name'],
                'latitude'      => $post['latitude'],
                'longitude'      => $post['longitude'],
                'type'      => $post['type'],
                'status'      => 'N',
                'other_address_name'      => $post['other_address_name'],
                'created_at' => Carbon::now(),
            ];

            if (!UserAddress::insert($insertUserAddress)) {
                throw new \Exception("Couldn't save user address.");
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function updateData($post)
    {
        try {
            $insertUserAddress = [
                'orgid'      => $post['orgid'],
                'userid'     => $post['userid'],
                'title'     => $post['title'],
                'name'     => $post['name'],
                'address_name'     => $post['address_name'],
                'latitude'      => $post['latitude'],
                'longitude'      => $post['longitude'],
                'type'      => $post['type'],
                'other_address_name'      => $post['other_address_name'],
                'updated_at' => Carbon::now(),
            ];

            $updated = UserAddress::where('id', $post['addressid'])
                ->update($insertUserAddress);

            if (!$updated) {
                throw new \Exception("Couldn't save user address.");
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getAllAddress($post)
    {
        try {
            $userid = DB::table('user_addresses')
                ->where('userid', $post['userid'])
                ->select('id', 'type', 'address_name', 'status', 'longitude', 'latitude')
                ->get();

            if (!$userid) {
                throw new Exception('No address available', 1);
            }

            return $userid;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function updateDataActive($post)
    {
        try {

            $insertUserAddress = [
                'status'      => 'N',
                'updated_at' => Carbon::now(),
            ];

            $updated = UserAddress::where('id', $post['addressid'])
                ->where('orgid', $post['orgid'])
                ->where('userid', $post['userid'])
                ->update($insertUserAddress);

            $insertUserAddress2 = [
                'status'      => 'Y',
                'updated_at' => Carbon::now(),
            ];

            $updated2 = UserAddress::where('id', $post['addressid'])
                ->where('orgid', $post['orgid'])
                ->where('userid', $post['userid'])
                ->update($insertUserAddress2);

            if (!$updated2) {
                throw new \Exception("Couldn't save customer active address.");
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getAddress($post)
    {
        try {
            $userid = DB::table('user_addresses')
                ->where('userid', $post['customerid'])
                ->select('latitude', 'longitude', 'address_name')
                ->where('status', 'Y')
                ->first();

            if (!$userid) {
                throw new Exception('Could not find address', 1);
            }

            return $userid;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
