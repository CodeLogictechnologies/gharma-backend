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
            $post['mobilenumber'] = '9876543210';
            $post['devicetoken'] = 'devicetoken';
            $post['devicename'] = 'iphone14';
            $post['devicetype'] = 'apple';

            $insertUserAddress = [
                'id'         => (string) Str::uuid(),
                'userid'     => $post['userid'],
                'mobilenumber'     => $post['mobilenumber'],
                'devicetoken'     => $post['devicetoken'],
                'devicename'     => $post['devicename'],
                'devicetype'      => $post['devicetype'],
                'created_at' => Carbon::now(),
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
