<?php

namespace App\Models;

use App\Mail\OtpMail;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Otp extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function sendOtp($post)
    {
        try {
            $otp = rand(100000, 999999);

            $insertUserAddress = [
                'id'         => (string) Str::uuid(),
                // 'userid'     => $post['userid'],
                'otp'     => $otp,
                'expires_at' => Carbon::now()->addMinutes(10),
                // 'orgid'     => $post['orgid'],
                'email'      => $post['email'],
                'created_at' => Carbon::now(),
            ];
            Mail::to($post['email'])->send(new OtpMail($otp));

            if (!Otp::insert($insertUserAddress)) {
                throw new \Exception("Couldn't save Otp.");
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}