<?php

namespace App\Models\BackPanel;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    public static function checkOtp($post)
    {
        try {
            $otp = Otp::where('email', $post['email'])->first(['otp', 'expires_at']);
            if ($otp && $otp->otp === $post['otp']) {
                if ($otp->expires_at && Carbon::now()->greaterThan($otp->expires_at)) {
                    throw new Exception("OTP has expired, please request a new one");
                }
                return true;
            } else {
                throw new Exception("OTP does not matched");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}