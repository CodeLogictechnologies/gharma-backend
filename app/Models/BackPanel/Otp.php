<?php

namespace App\Models\BackPanel;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    // Get registered email
    public static function checkOtp($post)
    {
        try {
            $otp = Otp::where('user_id', $post['id'])->first(['otp']);
            if ($otp && $otp->otp === $post['otp']) {
                // $dataArray = [
                //     'user_id' => $user->id,
                //     'otp' => Str::random(4),
                //     'created_at' => Carbon::now(),
                // ];
                // $userId = Otp::where('user_id', $user->id)->first(['user_id']);

                // if ($userId) {

                //     if (!Otp::where('user_id', $user->id)->update($dataArray)) {
                //         throw new Exception("Couldn't Save Records", 1);
                //     }
                // } else {
                //     if (!Otp::insert($dataArray)) {
                //         throw new Exception("Couldn't Save File", 1);
                //     }
                // }
                // return $post['id'];
                return true;
            } else {
                throw new Exception("OTP does not matched");
            }

            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
