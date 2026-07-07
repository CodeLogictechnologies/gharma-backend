<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;


class Userdevicetoken extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveDate($post)
    {
        try {
            $post['devicename'] = '';
            $post['devicetype'] = '';

            $exists = Userdevicetoken::where('userid', $post['userid'])
                ->where('devicetoken', $post['devicetoken'])
                ->exists();

            if ($exists) {
                return true;
            }

            $insertUserAddress = [
                'id'           => (string) Str::uuid(),
                'userid'       => $post['userid'],
                'mobilenumber' => $post['phone'],
                'devicetoken'  => $post['devicetoken'],
                'devicename'   => $post['devicename'],
                'devicetype'   => $post['devicetype'],
                'created_at'   => Carbon::now(),
            ];

            if (!Userdevicetoken::insert($insertUserAddress)) {
                throw new \Exception("Couldn't save user device token.");
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}